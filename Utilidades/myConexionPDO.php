<?php

  class mod_db
  {
    private $conexion; // Conexión a la base de datos
    private $perpage = 5; // Cantidad de registros por página
    private $total;
    private $pagecut_query;
    private $debug = false; // Cambiado a false para mantener la configuración original

    public function __construct()
    {
      
      ##### Setting SQL Vars #####

      $sql_host = "localhost";
      $sql_name = "expedientes";
      $sql_user = "root"; 
      $sql_pass = "demo";

      $dsn = "mysql:host=$sql_host;dbname=$sql_name;charset=utf8mb4";
      try {
        $this->conexion = new PDO($dsn, $sql_user, $sql_pass);
        $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Asegurar conexión con utf8mb4
        $this->conexion->exec("SET NAMES utf8mb4");
        if ($this->debug) {
          echo "Conexión exitosa a la base de datos<br>";
			}
		} catch (PDOException $e) {
			echo "Error de conexión: " . $e->getMessage();
			exit;
		}
	}

	public function getConexion (){

		return $this->conexion;
	}

	public function disconnect()
	{
		$this->conexion = null; // Cierra la conexión a la base de datos
	}

	public function insert($tb_name, $cols, $val)
	{
		$cols = $cols ? "($cols)" : "";
		$sql = "INSERT INTO $tb_name $cols VALUES ($val)";
    
			try {
				$this->conexion->exec($sql);
			} catch (PDOException $e) {
				echo "Error al insertar: " . $e->getMessage();
			}//fin del try
	}//fin del insert

public function insertSeguro($tb_name, $data)
{
    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));

    $sql = "INSERT INTO $tb_name ($columns) VALUES ($placeholders)";

    try {
        $stmt = $this->conexion->prepare($sql);

        // Asignar valores con bind
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "Error en INSERT: " . $e->getMessage();
        return false;
    }
}//fin del insertSeguro

	public function update($tb_name, $string, $astriction)
	{

		//UPDATE Work_Tickets SET Billed = true
		//WHERE UnitCost <> 0.00

		$sql = "UPDATE $tb_name SET $string where $astriction";
		//$this->executeQuery($sql, $astriction);
		  try {
       	 	$this->conexion->exec($sql);
   		  } catch (PDOException $e) {
        	echo "Error al Modificar: " . $e->getMessage();
    	  }//end try

	}//fin del update

	public function updateSeguro($tabla, $data, $condiciones)
{
    // Construir partes de SET dinámicamente
    $set = [];
    foreach ($data as $key => $value) {
        $set[] = "$key = :$key";
    }
    $setSQL = implode(", ", $set);
    // Construir partes de WHERE dinámicamente
    $where = [];
    foreach ($condiciones as $key => $value) {
        $where[] = "$key = :cond_$key";
    }
    $whereSQL = implode(" AND ", $where);
    $sql = "UPDATE $tabla SET $setSQL WHERE $whereSQL";

    try {
        $stmt = $this->conexion->prepare($sql);

        // Bind de los datos a actualizar
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        // Bind de las condiciones (prefijadas con "cond_")
        foreach ($condiciones as $key => $value) {
            $stmt->bindValue(":cond_$key", $value);
        }

        return $stmt->execute();
    } catch (PDOException $e) {
        echo "Error en UPDATE: " . $e->getMessage();
        return false;
    }
}//fin del updateSeguro


	public function del($tb_name, $astriction)
	{
		$sql = "DELETE FROM $tb_name";
		if ($astriction) {
			$sql .= " WHERE $astriction"; // Agrega la restricción si existe
		}
		$this->executeQuery($sql);
	}

	


	public function log($Usuario){

	 // Preparar la consulta

		 try {
		 $sql = "SELECT * FROM usuarios WHERE Usuario = :User";
		 $stmt = $this->conexion->prepare($sql);
		 $stmt->bindParam(':User', $Usuario, PDO::PARAM_STR);

		 // Ejecutar la consulta
		 $stmt->execute();

			// Retornar el objeto directamente
            return $stmt->fetchObject();
		
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
            return null;
		}

	} //log(usuario)

	public function executeQuery($string)
	{
		
		try {
			$stmt = $this->conexion->prepare($string);
			$stmt->execute();
			return $stmt;
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			return null;
		}
	}//fin del executeQuery 

	
	public function nums($string = "", $stmt = null)
	{
		if ($string) {
			$stmt = $this->executeQuery($string);
		}
		if ($stmt!=null)
		{
			$this->total = $stmt ? $stmt->rowCount() : 0; // Cuenta el número de filas
			return $this->total;
		}else {
			return 0;
		}
	}





	//Este mi object para resolver los asuntos de las consultas con Limit
	public function objects($stmt = "")
	{
		return $stmt ? $stmt->fetch(PDO::FETCH_OBJ) : null; // Retorna un objeto
	}

	
	public function Arreglos($string = "")
	{
		$stmt = "";
		
		
			try {
				if ($string) {
					$stmt = $this->conexion->query($string);
				}
			 
			} catch (PDOException $e) {
    			echo "Error: " . $e->getMessage();
			}

			return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
			//return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null; // Retorna un objeto
			

	}

	public function insert_id()
	{
		return $this->Conexion->lastInsertId(); // Retorna el último ID insertado
	}



	
}
