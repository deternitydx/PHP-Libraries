<?php
	include_once("/config/config.php");
	
	/**
	 * Database Class File
	 *
	 * Contains Database class
	 * @package Repository
	 * @subpackage Database
	 *
	 * @author Robbie Hott
	 */
	 
	 
	/**
	 * Database Handler
	 * 
	 * Handles all database interactions, providing OO front-end to PHP's database calls.  Also allows for plug-and-play capability
	 * for other databases, if the need should arise.  
	 *
	 * @version 1.0
	 * 
	 */ 
class Database {
	
	var $config;
	var $link;
	
	/**
	 * connect to database
	 *
	 * Basic connection function, connects to database defined in config.php.
	 */
	function connect() {
		global $dbinfo;
		$config = $dbinfo;
		$this->link = mysql_connect($config['server'], $config['userid'], $config['passwd']) or die('Could not connect to the database server.');
		mysql_select_db($config['dbname']) or die('Could not connect to the '.$config['dbname'].' database.');
		return;
	}
	
	/**
	 * connect to database
	 *
	 * Connects to a database as defined in the parameter $config, defined as an array
	 *
	 *        'server' => server address/name to connect to<br>
	 *        'userid' => userid for this connection<br>
	 *        'passwd' => password for the user (in clear text)<br>
	 *        'dbname' => database to select
	 * 
	 * @param array $config array defined as above.
	 */
	function connect2($this_config) {
                $this->link = mysql_connect($this_config['server'], $this_config['userid'], $this_config['passwd']) or die('Could not connect to the database server.');
                mysql_select_db($this_config['dbname']) or die('Could not connect to the '.$this_config['dbname'].' database.');
                return;
        }

	/**
	 * select database
	 *
	 * Selects another database with this connection.
	 * 
	 * @param string $db database to select
	 * @return boolean true on success, false on failure
	 */
	function select_db($db) {
		if (mysql_select_db($db))
			return true;
		else
			return false;
	}
		
	/**
	 * disconnect from database
	 *
	 * Terminate the mysql connection to the database.
	 * 
	 */
	function disconnect() {
		mysql_close($this->link);
	}


	/**
	 * get last insert id
	 *
	 * Returns the id of the last insert operation
	 *
	 * @return int id of the last insert
	 */
	function get_last_id() {
		return mysql_insert_id($this->link);
	}

	/**
	 * generic query
	 *
	 * Performs the parameter $query on the database and returns the result.
	 * 
	 * @param string $query query to perform
	 * @return resource raw mysql query result
	 */
	function query($query) {
		//echo "QUERY: $query<br/>";
		$result = mysql_query($query, $this->link) or die('Query failed: '.mysql_error());
		// echo "Query: " .$result . "\n";
		return $result;
	}
	
	/**
	 * mysql select statement
	 *
	 * Selects information from the table parameters given and returns the result as a PHP array.
	 * 
	 * @param string $table table to select from
	 * @param string $cols columns to return
	 * @param string $where where statement to use, use "1" if no limits should be placed
	 * @return array results in array form
	 */
	function select($table, $cols, $where) {
		$query = "SELECT $cols FROM $table WHERE $where";
		$result = mysql_query($query, $this->link) or die('Query failed: '.mysql_error());
		$results = array();
		while ($temp = mysql_fetch_array($result))
			array_push($results, $temp);
		//echo "Select ";
		//print_r($results);
		return $results;
	}
	
	/**
	 * mysql insert statement
	 *
	 * Inserts information into the given table.
	 * 
	 * @param string $table table to insert into
	 * @param array $data data to be inserted as a PHP array: indices are column names in the mysql database and values are data to insert
	 * @return resource mysql query result
	 */
	function insert($table, $data) {
		$key = "";
		$val = "";
		foreach ($data as $k=>$v) {
			$key .= "`".$k."`,";
			if ($v != 'NULL')
				$val .= "'".$v."',";
			else
				$val .= $v.",";
		}
		$key = "(".substr($key,0,-1).")";
		$val = "(".substr($val,0,-1).")";
		$query = "INSERT INTO $table $key VALUES $val";
		//echo $query;
		$result = mysql_query($query, $this->link) or die('Query failed: '.mysql_error());
		return $result;
	}
		
	/**
	 * mysql insert statement
	 *
	 * Inserts information into the given table.
	 * 
	 * @param string $table table to insert into
	 * @param array $keys indices are column names in the mysql database
	 * @param array $data data to insert
	 * @return resource mysql query result
	 */
	function insert_separate($table, $keys, $data) {
		$key = "";
		$val = "";
		foreach ($data as $k=>$v) {
			$key .= "`".$keys[$k]."`,";
			if ($v != 'NULL')
				$val .= "'".$v."',";
			else
				$val .= $v.",";
		}
		$key = "(".substr($key,0,-1).")";
		$val = "(".substr($val,0,-1).")";
		$query = "INSERT INTO $table $key VALUES $val";
		//echo $query;
		$result = mysql_query($query, $this->link) or die('Query failed: '.mysql_error());
		return $result;
	}
		
	/**
	 * mysql update statement
	 *
	 * Updates information in the given table.
	 * 
	 * @param string $table table to insert into
	 * @param array $data data to be inserted as a PHP array: indices are column names in the mysql database and values are data to insert
	 * @param string $where where statement: use "1" if not limited
	 * @return resource mysql query result
	 */
	function update($table, $data, $where) {
		$key = "";
		foreach ($data as $k=>$v) {
			if ($v != "NULL")
				$key .= "`".$k."`='".$v."',";
			else
				$key .= "`".$k."`=".$v.",";

		}
		$key = substr($key,0,-1);
		$query = "UPDATE $table SET $key WHERE $where";
		echo $query;
		$result = mysql_query($query, $this->link) or die('Query failed: '.mysql_error());
		return $result;
	}





}
