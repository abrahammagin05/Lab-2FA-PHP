# 🔐 Laboratorio — Autenticación con 2FA en PHP

<div align="center">

![gif-principal](https://media.giphy.com/media/077i6AULCXc0FKTj9s/giphy.gif)

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-2.x-885630?style=for-the-badge&logo=composer&logoColor=white)
![2FA](https://img.shields.io/badge/2FA-Google_Authenticator-4285F4?style=for-the-badge&logo=google&logoColor=white)
![WampServer](https://img.shields.io/badge/WampServer-3.x-orange?style=for-the-badge&logo=apache&logoColor=white)
![CSRF](https://img.shields.io/badge/CSRF-Protegido-green?style=for-the-badge&logo=shield&logoColor=white)

**Universidad Tecnológica de Panamá**  
Facultad de Ingeniería en Sistemas Computacionales  
Campus Victor Levis Sasso

</div>

---

## 📋 Tabla de Contenidos

- [Objetivo del Laboratorio](#-objetivo-del-laboratorio)
- [Requisitos Previos](#-requisitos-previos)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Configuración de la Base de Datos](#-configuración-de-la-base-de-datos)
- [Instalación y Configuración](#-instalación-y-configuración)
- [Flujo del Sistema](#-flujo-del-sistema)
- [Clases Principales](#-clases-principales)
- [Pruebas de Ejecución](#-pruebas-de-ejecución)
- [Protección CSRF](#-protección-csrf)
- [Dificultades y Soluciones](#-dificultades-y-soluciones)
- [Referencias](#-referencias)
- [Desarrollado por](#-desarrollado-por)

---

## 🎯 Objetivo del Laboratorio

Implementar un sistema de autenticación seguro en PHP con **Autenticación de Dos Factores (2FA)** usando Google Authenticator, aplicando buenas prácticas de seguridad web.

| # | Objetivo |
|---|---|
| 1️⃣ | Implementar **2FA** con Google Authenticator (TOTP) |
| 2️⃣ | Crear usuarios MySQL con **privilegios mínimos** (sin root) |
| 3️⃣ | Aplicar **sanitización de datos** con clase dedicada |
| 4️⃣ | Proteger formularios contra ataques **CSRF** |
| 5️⃣ | Implementar **hashing seguro** de contraseñas con BCrypt |
| 6️⃣ | Gestionar **sesiones** en dos fases (pre-auth y autenticado) |

---

## 🛠️ Requisitos Previos

### Ecosistema de Desarrollo

| Tecnología | Versión | Descripción |
|---|---|---|
| ![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white) **PHP** | 8.0 o superior | Lenguaje de programación del servidor |
| ![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white) **MySQL** | 5.7 o superior | Base de datos relacional |
| ![Composer](https://img.shields.io/badge/Composer-885630?logo=composer&logoColor=white) **Composer** | 2.x | Gestor de dependencias PHP |
| ![WampServer](https://img.shields.io/badge/WampServer-orange?logo=apache&logoColor=white) **WampServer** | 3.x | Entorno de desarrollo local |
| ![VSCode](https://img.shields.io/badge/VS_Code-007ACC?logo=visualstudiocode&logoColor=white) **Visual Studio Code** | Última versión | Editor de código |
| ![Git](https://img.shields.io/badge/Git-F05032?logo=git&logoColor=white) **Git** | Última versión | Control de versiones |
| 📱 **Google Authenticator** | Última versión | App móvil para códigos TOTP |

---

## 🗂️ Estructura del Proyecto

```
Lab-2FA-PHP/
│
├── 📄 index.php                    ← Procesa el login (fase 1)
├── 📄 login.php                    ← Página del formulario de login
├── 📄 login_form.php               ← Formulario HTML del login
├── 📄 verificar_2fa.php            ← Verificación del código 2FA (fase 2)
├── 📄 procesar_registro.php        ← Procesa el registro y genera QR
├── 📄 hash_tool.php                ← Interfaz para generar y validar hashes
├── 📄 salir.php                    ← Cierre de sesión
├── 📄 composer.json                ← Dependencias del proyecto
│
├── 📁 clases/
│   ├── mysql.inc.php               ← Clase de conexión PDO
│   ├── SanitizarEntrada.php        ← Clase de sanitización (5 métodos)
│   ├── RegistroUsuario.php         ← Clase de registro de usuario
│   ├── objLoginAdmin.php           ← Clase de validación de login
│   └── VerificarCorreo.php         ← Verifica correo duplicado (AJAX)
│
├── 📁 comunes/
│   ├── bloque_Seguridad.php        ← Guard de sesión
│   ├── loginfunciones.php          ← Funciones auxiliares
│   ├── Cabecera4.php               ← Cabecera del sitio
│   └── footer.php                  ← Pie de página
│
├── 📁 formularios/
│   └── registrese.php              ← Formulario de registro con validación
│
├── 📁 Utilidades/
│   └── CSRFProtection.php          ← Clase de protección CSRF
│
├── 📁 css/                         ← Estilos del proyecto
├── 📁 Estilos/                     ← Estilos adicionales
└── 📁 vendor/                      ← Dependencias Composer (excluido del repo)
```

---

## 🗄️ Configuración de la Base de Datos

### 1️⃣ Crear la base de datos y tablas

```sql
CREATE DATABASE company_info CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE company_info;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  Nombre VARCHAR(255) NOT NULL,
  Apellido VARCHAR(255) NOT NULL,
  Sexo ENUM('M','F','Otro') NOT NULL,
  Usuario VARCHAR(100) NOT NULL UNIQUE,
  Correo VARCHAR(100) NOT NULL UNIQUE,
  HashMagic VARCHAR(255) NOT NULL,
  secret_2fa VARCHAR(255) NULL,
  FechaSistema DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE intentos_login (
  id INT AUTO_INCREMENT PRIMARY KEY,
  Usuario VARCHAR(100),
  ipRemoto VARCHAR(100),
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  deteccion_anomalia TINYINT(1) DEFAULT 0
);

CREATE TABLE trazabilidad_acciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  Tabla VARCHAR(100),
  Acciones VARCHAR(50),
  CodigoRegistro INT,
  Usuario VARCHAR(100),
  FechaSistema DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 2️⃣ Crear usuario con privilegios mínimos

```sql
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'mySecreto27';

GRANT SELECT, INSERT, UPDATE ON company_info.usuarios TO 'admin'@'localhost';
GRANT SELECT, INSERT ON company_info.intentos_login TO 'admin'@'localhost';
GRANT SELECT, INSERT ON company_info.trazabilidad_acciones TO 'admin'@'localhost';

FLUSH PRIVILEGES;

-- Ver privilegios concedidos
SHOW GRANTS FOR 'admin'@'localhost';
```

> ⚠️ **Importante:** Nunca usar `root` en la conexión de la aplicación. El usuario `admin` solo tiene los permisos estrictamente necesarios.

### 📸 Privilegios del usuario (SHOW GRANTS)

<!-- 🖼️ PEGA AQUÍ SCREENSHOT DE SHOW GRANTS FOR 'admin'@'localhost' -->
![screenshot-grants](PEGA_AQUI_SCREENSHOT_SHOW_GRANTS)

---

## ⚙️ Instalación y Configuración

### 1️⃣ Clonar el repositorio

```bash
cd C:\wamp\www
git clone https://github.com/TU_USUARIO/Lab-2FA-PHP
cd Lab-2FA-PHP
```

### 2️⃣ Instalar dependencias

```bash
composer require sonata-project/google-authenticator
```

### 3️⃣ Configurar la conexión en `clases/mysql.inc.php`

```php
$sql_host = "localhost";
$sql_name = "company_info";
$sql_user = "admin";       // Usuario con privilegios mínimos
$sql_pass = "mySecreto27";
```

### 4️⃣ Ejecutar el SQL de la base de datos

Importar las tablas en phpMyAdmin desde la pestaña SQL.

### 5️⃣ Sincronizar la hora del sistema

> 📌 **Crítico para 2FA:** La hora del servidor y del celular deben estar sincronizadas. Una diferencia mayor a 30 segundos hace que los códigos no coincidan.

- **Windows:** `Configuración → Hora e idioma → Fecha y hora → Sincronizar ahora`
- **iPhone:** `Configuración → General → Fecha y hora → Ajustar automáticamente`

---

## 🔄 Flujo del Sistema

```
1. REGISTRO
   └─ Formulario de registro (registrese.php)
   └─ Validación frontend con jQuery Validate
   └─ Verificación AJAX de correo duplicado
   └─ Sanitización de datos (SanitizarEntrada)
   └─ Hash BCrypt de contraseña (cost: 13)
   └─ Generación de secreto 2FA
   └─ Muestra código QR para escanear con Google Authenticator

2. LOGIN — FASE 1 (usuario + contraseña)
   └─ Verificación CSRF
   └─ Sanitización del input
   └─ Búsqueda del usuario en BD
   └─ Verificación del hash con password_verify()
   └─ Registro del intento en tabla intentos_login
   └─ Si es exitoso → sesión temporal $_SESSION['pre_auth_user']
   └─ Redirige a verificar_2fa.php

3. LOGIN — FASE 2 (código Google Authenticator)
   └─ Verifica que existe sesión temporal
   └─ Obtiene secret_2fa del usuario en BD
   └─ Valida código TOTP con GoogleAuthenticator::checkCode()
   └─ Si es correcto → destruye sesión temporal
   └─ Crea sesión definitiva $_SESSION['autenticado'] = "SI"
   └─ Redirige al panel de control

4. PÁGINAS PROTEGIDAS
   └─ bloque_Seguridad.php verifica $_SESSION['autenticado'] == "SI"
   └─ Si no está autenticado → redirige al login
```

---

## 📦 Clases Principales

### `SanitizarEntrada` — Métodos estáticos de sanitización

| Método | Descripción |
|---|---|
| `CadTitulo($valor)` | Sanitiza nombres/apellidos, permite tildes |
| `limpiarCadena($valor)` | Solo letras, números y guión bajo |
| `limpiarEspacios($valor)` | Elimina espacios y caracteres peligrosos |
| `sanitizarEmail($valor)` | Valida y sanitiza correos electrónicos |
| `validarSexo($valor)` | Valida que sea M, F u Otro |

### `RegistroUsuario` — Gestión del registro

| Método | Descripción |
|---|---|
| `__construct($datos, $pdo, &$arrMensaje)` | Recibe y sanitiza datos del POST |
| `encriptarClave()` | Genera hash BCrypt con cost 13 |
| `Guardar_RegistroUsuario()` | Inserta en BD y registra en trazabilidad |
| `GuardarMySecreto($secreto)` | Guarda el secret_2fa en BD |
| `getUsuario()` | Retorna el nombre de usuario |

### `CSRFProtection` — Protección contra CSRF

| Método | Descripción |
|---|---|
| `generarToken()` | Genera token aleatorio de 32 bytes |
| `campoHidden()` | Genera `<input hidden>` para formularios |
| `verificarFormulario()` | Valida token del POST, mata script si falla |
| `metaTag()` | Genera meta tag para peticiones AJAX |

---

## 📸 Pruebas de Ejecución

### ✅ Formulario de Registro

<!-- 🖼️ PEGA AQUÍ SCREENSHOT DEL FORMULARIO DE REGISTRO -->
![screenshot-registro](PEGA_AQUI_SCREENSHOT_FORMULARIO_REGISTRO)

### ✅ Código QR generado tras el registro

<!-- 🖼️ PEGA AQUÍ SCREENSHOT DEL QR GENERADO -->
![screenshot-qr](PEGA_AQUI_SCREENSHOT_QR)

### ✅ Login — Formulario principal

<!-- 🖼️ PEGA AQUÍ SCREENSHOT DEL LOGIN -->
![screenshot-login](PEGA_AQUI_SCREENSHOT_LOGIN)

### ✅ Verificación 2FA

<!-- 🖼️ PEGA AQUÍ SCREENSHOT DE LA PÁGINA DE VERIFICACIÓN 2FA -->
![screenshot-2fa](PEGA_AQUI_SCREENSHOT_2FA)

### ✅ Herramienta de Hash

<!-- 🖼️ PEGA AQUÍ SCREENSHOT DEL HASH TOOL -->
![screenshot-hash](PEGA_AQUI_SCREENSHOT_HASH_TOOL)

### ✅ Tablas en phpMyAdmin

<!-- 🖼️ PEGA AQUÍ SCREENSHOT DE LAS TABLAS CON DATOS -->
![screenshot-tablas](PEGA_AQUI_SCREENSHOT_TABLAS)

---

## 🛡️ Protección CSRF

La metodología implementada usa tokens **Anti-CSRF**:

1. Al cargar el formulario se genera un token aleatorio de 32 bytes con `random_bytes(32)`
2. El token se guarda en `$_SESSION['csrf_token']`
3. Se incluye como campo oculto en cada formulario
4. Al procesar el POST se compara con `hash_equals()` para evitar timing attacks
5. Si los tokens no coinciden → error 403 → ataque bloqueado

```php
// En el formulario
echo CSRFProtection::campoHidden();

// Al procesar
CSRFProtection::verificarFormulario();
```

> ✅ El atacante no puede acceder al valor del token porque la **política de mismo origen** impide leer la respuesta del servidor desde otro dominio.

---

## ⚠️ Dificultades y Soluciones

### ❓ Dificultad 1 — Código 2FA siempre incorrecto

**Error encontrado:** El código de Google Authenticator no coincidía aunque era correcto.

**Causa:** La hora del servidor (PC) y del celular estaban desincronizadas por más de 30 segundos.

**Solución aplicada:**
- Windows: `Configuración → Hora e idioma → Sincronizar ahora`
- iPhone: Activar `Ajustar automáticamente`

---

### ❓ Dificultad 2 — `lastInsertId()` retornaba null

**Error encontrado:**
```
Fatal error: Call to a member function lastInsertId() on null
```

**Causa:** El método `insert_id()` usaba `$this->Conexion` con C mayúscula en lugar de `$this->conexion`.

**Solución aplicada:**
```php
// ❌ Incorrecto
return $this->db->lastInsertId();

// ✅ Correcto
return $this->conexion->lastInsertId();
```

---

### ❓ Dificultad 3 — Error `#1071` al crear tablas

**Error encontrado:**
```
#1071 - Declaración de clave demasiado larga. La máxima longitud de clave es 1000
```

**Causa:** La versión de MySQL en WAMP no soporta índices UNIQUE en `VARCHAR(255)` con `utf8mb4`.

**Solución aplicada:** Reducir a `VARCHAR(100)` en los campos con `UNIQUE`.

---

### ❓ Dificultad 4 — `secret_2fa` no se guardaba en BD

**Error encontrado:** La columna `secret_2fa` quedaba NULL después del registro.

**Causa:** El método `updateSeguro()` no existía en la clase `mod_db` del repo base.

**Solución aplicada:** Agregar el método `updateSeguro()` a `mysql.inc.php` con PDO preparado y binding dinámico.

---

## 🎒 Referencias

1. **Video del Laboratorio proporcionado por la Instructora**  
   🔗 https://www.youtube.com/watch?v=eSV31N-kC60

2. **Repositorio base del ejemplo (Ing. Irina Fong)**  
   🔗 https://github.com/Salomon2514/EjemploLogin

3. **Librería Google Authenticator para PHP**  
   🔗 https://packagist.org/packages/sonata-project/google-authenticator

4. **PHP — password_hash()**  
   🔗 https://www.php.net/manual/es/function.password-hash.php

5. **PHP — filter_var() y FILTER_SANITIZE_EMAIL**  
   🔗 https://www.php.net/manual/es/function.filter-var.php

6. **OWASP — Cross-Site Request Forgery (CSRF)**  
   🔗 https://owasp.org/www-community/attacks/csrf

7. **Ley 81 de Protección de Datos Personales de Panamá**  
   🔗 https://www.gacetaoficial.gob.pa

---

## 📅 Fecha de Ejecución del Laboratorio

| Detalle | Información |
|---|---|
| 📆 Fecha de entrega | I Semestre 2026 |
| 💻 Entorno | Windows + WampServer + VS Code |
| 📱 App 2FA | Google Authenticator (iPhone) |

---

<div align="center">

## 👨‍💻 Desarrollado por

![gif-footer](https://media.giphy.com/media/LnQjpWaON8nhr21vNW/giphy.gif)

**Este laboratorio ha sido desarrollado por estudiantes de la Universidad Tecnológica de Panamá:**

| Campo | Información |
|---|---|
| 👤 **Nombre** | Abraham Magin |
| 📧 **Correo** | abraham.magin@utp.ac.pa |
| 📚 **Curso** | Desarrollo Web VII |
| 👩‍🏫 **Instructora** | Ing. Irina Fong |
| 🏫 **Universidad** | Universidad Tecnológica de Panamá |
| 🏛️ **Facultad** | Ingeniería en Sistemas Computacionales |
| 🎓 **Carrera** | Lic. Desarrollo y Gestión de Software |

---

*Campus Victor Levis Sasso — I Semestre 2026*

</div>
