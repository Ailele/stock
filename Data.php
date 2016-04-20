<?php 
	namespace Stock\Data;

	use mysqli;

	class Data
	{
		protected $database;
		protected $config;

		public function __construct($config)
		{
			$this -> config = $config;
			$this -> initDatabase();
		}

		public function checkTime()
		{
			$sql = "select date from log";
			$query = $this -> database -> query($sql);
			$resutl = $query -> fetch_all(MYSQLI_ASSOC);
			return $resutl[0]['date'];
		}

		public function updateTime($time)
		{
			$sql = "update log set date ='$time';";
			$query = $this -> database -> query($sql);
			
			return $query;
		}

		public function clear()
		{
			$sql = "truncate table stock;";
			$this -> database -> query($sql);
			$sql = "truncate table history;";
			$this -> database -> query($sql);
		}
		
		public function initDatabase()
		{
			$connect = new mysqli($this -> config['DB_HOST'], 
								  $this -> config['DB_USERNAME'], 
								  $this -> config['DB_PASSWORD']);

			if(!$connect)
			{
					exit('connect database error');
			}
			else
			{
				$isTable = $connect -> query('use '.$this -> config['DB_DATABASE']);
				if(!$isTable)
				{
					$connect -> query('create database '.$this -> config['DB_DATABASE']);
				}
			}

			return $this -> database = $connect;
		}

		public function getStockListFromDB()
		{
			$select = "select stockCode from stock";

			
		}

		public function createTable($name, $stockCodeName, array $info)
		{
			$createSQL = "create table $name(";
			while( $type = current($info))
			{
				$column = key($info);

				if(!next($info))
				{
					$createSQL .= "$column $type";
				}
				else
				{
					if($stockCodeName == $column)
					{
						$createSQL .= "$column $type primary key,";
					}
					else
					{
						$createSQL .= "$column $type,";
					}
				}
			}

			$createSQL .= ');';
			return $this -> database -> query($createSQL);
		}

		public function storeStock($tableName, array $info)
		{
			$addSQL = "insert $tableName";
			$columnName = '';
			$columnValue = '';

			while( $value = current($info))
			{
				$name = key($info);

				if(!next($info))
				{
					$columnName .= "$name";
					$columnValue .= "'$value'";
				}
				else
				{
					$columnName .= "$name,";
					$columnValue .= "'$value',";
				}
			}
			$addSQL = $addSQL."(".$columnName.") values(".$columnValue.");";
			return $this -> database -> query($addSQL);
		}

		public function storeHistory($tableName, array $history)
		{
			$storeSQL = "insert $tableName";
			$columnName = '';
			$columnValue = '';

			while( $value = current($history))
			{
				$name = key($history);

				if(!next($history))
				{
					$columnName .= "$name";
					$columnValue .= "'$value'";
				}
				else
				{
					$columnName .= "$name,";
					$columnValue .= "'$value',";
				}
			}

			$storeSQL = $storeSQL."(".$columnName.") values(".$columnValue.");";
			return $this -> database -> query($storeSQL);
		}

		public function updateHistory($tableName, array $history)
		{
			$updateSQL = "update table $name set ";

			while( $value = current($info))
			{
				$name = key($info);

				if(!next($info))
				{
					$updateSQL .= " $name = $value";
				}
				else
				{
					$updateSQL = " $name = $value,";
				}
			}

			$updateSQL .= "where stockCode = '".$stock."';";

			return $this -> database -> query($updateSQL);
		}

		public function updateStock($name, $stock, $info)
		{
			$updateSQL = "update table $name set ";

			while( $value = current($info))
			{
				$name = key($info);

				if(!next($info))
				{
					$updateSQL .= " $name = $value";
				}
				else
				{
					$updateSQL = " $name = $value,";
				}
			}

			$updateSQL .= "where stockCode = '".$stock."';";

			return $this -> database -> query($updateSQL);
		}

		public function getStockInfo($table, $stockCode)
		{
			$getSQL = "select * from $table where stockCode ='$stockCode';";
			$query = $this -> database -> query($getSQL);

			if($query)
			{
				return $query -> fetch_all(MYSQLI_ASSOC);
			}
			else
			{
				return false;
			}
		}

		public function getTopCom($top)
		{
			$SQL = "select * from stock order by commission desc limit $top;";
			$query = $this -> database -> query($SQL);
			$top = $query -> fetch_all(MYSQLI_ASSOC);

			return $top;
		}

		public function changeDatabase($database)
		{
			return $this -> database -> query('use '.$database);
		}
	}