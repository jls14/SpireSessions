<?php
//ConnDB.php
class ConnDB
{
	//hash for connection to postgres db
	private static $conn_hash = [
		"host"    =>"localhost",
		"port"    => 5432,
		"dbname"  =>"enderrac_spiresessions",
		"user"    =>"enderrac_dev",
		"password"=>"SpireSessions_5"
	];

	//connection variable
	private static $conn;

	//is connection set
	private static $is_conn_set = false;

	//get the string for connection based on self::$conn_hash
	private static function get_conn_str()
	{
		$conn_str = "";
		foreach (self::$conn_hash as $key => $value){
			$conn_str .= "$key=$value ";
		}
		return $conn_str;
	}

	//create pg connection
	public static function set_conn()
	{
		if(!self::$is_conn_set){
			self::$conn = pg_connect(self::get_conn_str()) 
				or die('Query failed: ' . pg_last_error());
			self::$is_conn_set = isset(self::$conn);
		}
	}

	//close the connection
	public static function close_conn()
	{
		if(self::$is_conn_set)
		{
			pg_close(self::$conn);
			self::$is_conn_set = false;
		}
	}

	//query the connected db
	public static function query_db(/*string*/$query)
	{	
		//echo $query;
		if(!self::$is_conn_set){
			self::set_conn();
		}

		return pg_query(self::$conn, $query);
	}
}
?>