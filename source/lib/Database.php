<?php

/**
* Establishes connection to our MySQL Database.
*/
class Database
{
	/**
	 * MySQL Database Settings
	 * gets from configure.php
	 */
	const DBHOST = DB_HOST;
	const DBUSER = DB_USER;
	const DBPASSWORD = DB_PASS;
	const DBNAME = DB_NAME;
	const DBPORT = DB_PORT;

	private $_response = ['status'=>true];
	
	public function connect(){
		try{

		 	#MySQL with PDO
			$DBH = new PDO('mysql:host='.self::DBHOST.';port='.self::DBPORT.';dbname='.self::DBNAME, self::DBUSER, self::DBPASSWORD); //
			//curently set to show warning
			$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			return $DBH;
		}
		catch(PDOException $e) {
			$this->_response['status'] = false;
			$this->_response['reason'] = 'Database Error:'.$e->getMessage();
		}
	}
	public function getResponse(){
		return $this->_response;
	}
}