<?php namespace Vimeochallenge\Source;

####################################################################################
#####                     VIMEO CHALLENGE - DATA DUMPER                       ######
#####                         Author: Marvin Aleman                           ######
#####                          Created: 02/08/2017                            ######
####################################################################################


/**
 * This class will self-initiate once this file is ran.
 * Please enter your Database settings in the Class's Constants
 * The Database will be created using the DBNAME. All tables will also be created.
 * Make sure to provide a DB User with proper Create Access
 * Once Database connected and created has been established, we will read through
 * data.dump file provided line by line.
 * I decided to enter all country codes from the REGISTER inserts in its own 'countries' table.
 * Country is now referenced as a integer identifier and better for indexing.
 * likes, watch and other totals are now stored as static value within USER and VIDEOS table.
 * This will allow quicker access to totals should there be larger DB files.
 * A simple error log will be created in the directory.
 */
use \PDO;

class Dump
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
	const MAXREAD = DUMP_READ;
	const CHUNKSIZE = DUMP_CHUNK;

	private $_starttime;
	private $_dbh;
	private $_countries = [];
	private $_successful = 0;
	private $_errors = 0;
	private $_register_inserts = ['total'=>0];
	private $_upload_inserts = ['total'=>0];
	private $_watch_inserts = ['total'=>0];
	private $_like_inserts = ['total'=>0];
	private $_line = 1;//start line, let's hold it in param to use in other methods
	/**
	 * Parameter with array of available INSERT SQL Statemnts
	 */
	private $_recordTypeSql = [
			'REGISTER' => 'INSERT INTO users (user_id,created,country_id,ip_address) VALUES ',
			'UPLOAD' => 'INSERT INTO videos (video_id,upload_date,user_id) VALUES ',
			'WATCH' => 'INSERT INTO videos_watch_log (video_id,watch_date,user_id) VALUES ',
			'LIKE' => 'INSERT INTO videos_likes_log (video_id,like_date,user_id) VALUES ',
			'COUNTRY' => 'INSERT INTO countries (country_code) VALUES '
		];

	public function __construct(){
		$this->_starttime = microtime(true);
		echo LINEBREAK, 'Initiating Datadump...',LINEBREAK;
		$this->_dbh = self::dbConnect();
		$this->init();
	}
	/**
	 * Establish PDO Connection to DB and store it inside parameter
	 */
	final private function dbConnect(){
		try{

		 	#MySQL with PDO_MYSQL
			$DBH = new PDO('mysql:host='.self::DBHOST.';port='.self::DBPORT.';', self::DBUSER, self::DBPASSWORD); //
			//curently set to show warning
			$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			echo LINEBREAK,'CONNECTED TO DATABASE....',LINEBREAK;
			return $DBH;
		}
		catch(PDOException $e) {
			die('Database Error'.$e->getMessage());
		}
	}
	/**
	 * Basic error logging method
	 */
	final private function logError($info){
		//insert error in log
		$errorFile = fopen(__DIR__.'\error_log.txt', 'a');
		fwrite($errorFile,date('Y-m-d H:i:s')."\t".$this->_line."\t".$info.PHP_EOL);
		fclose($errorFile);
	}
	/**
	 * Method used to execute DB Query.
	 * If params argument left out, it will process SQL without any parameters.
	 */
	final private function runSql(string $sql,array $params=NULL){
		$response = ['status'=>true,'message'=>''];

		$this->_dbh->beginTransaction();

		try {
			//lets check if the params is a multidimensional array
			$insert_array = [];

			//if params is an array lets see if it is a multiinsert
			if(is_array($params) && is_array($params[0])){
				//lets get the total col from the first index
				$cols = count($params[0]);
				$positionals = '('.substr(str_repeat(',?',$cols),1).')';
				$inserts = array_fill(0,count($params),$positionals);
				$sql .= implode(',',$inserts);

				//now lets create one single flat array
				for($i=0;$i<count($params);$i++){
					foreach($params[$i] as $k => $v){
						$insert_array[] = trim($v);
					}
				}
			//if it is a regular array
			}elseif(is_array($params)){
				$positionals = [];

				foreach($params as $p){
					$positionals[] = '?';
					$insert_array[] = $p;
				}
				$sql .= '('.implode(",",$positionals).')';
			//submit normally and treat param as null
			}else{
				$insert_array = NULL;
			}

			$STH = $this->_dbh->prepare($sql);
			$STH->execute($insert_array);

			if($STH->errorCode() != 0){
				$err_msg = $STH->errorInfo();
				throw new Exception('Database error:'.$STH->errorCode().'-'.$err_msg[2]);
			}else{
				$response['message'] = $this->_dbh->lastinsertid();
			}
			$this->_dbh->commit();
			$STH = null;
			$sql = null;
			$insert_array = null;
			$positionals = null;

		} catch (Exception  $e) {
			$this->_dbh->rollBack();
		    $response['status'] = false;
		    $response['message'] = $e->getMessage();
			echo 'Error: ', $response['message'], PHP_EOL;
			$this->logError($response['message']);
		    $this->_errors++;
		}

		return $response;
	}
	/**
	 * Method used to determine which INSERT SQL statement to used based on type
	 */
	final private function insertSQL($type,$final=false){
		$valid = ['REGISTER','UPLOAD','WATCH','LIKE'];
		if(!in_array($type, $valid)) die("Invalid 'type' for insertSQL");

		$paramName = '_'.strtolower($type).'_inserts';

		//if this is a 'final' request, done at the end of the loop to see if there are any unsubmitted inserts
		if($this->$paramName['total'] >= self::CHUNKSIZE || ($final && isset($this->$paramName['records']) && count($this->$paramName['records'])>0)){

			$newInsert = $this->runSql($this->_recordTypeSql[$type],$this->$paramName['records']);
			if($newInsert['status']){
				echo $this->$paramName['total']," {$type} CHUNK INSERTS Added",PHP_EOL;
				$this->_successful+=$this->$paramName['total'];
			}
			//now lets clear _register_inserts
			$this->$paramName['total'] = 0;
			unset($this->$paramName['records']);
		}
	}
	/**
	 * Method used to process each line within data.dump
	 */
	final public function processRecord($line){
		$record = explode(' ', $line);
		$ok = true;

		//if REGISTER type, let's first check to see if country code exists in '_countries' property
		if ( $record['1'] == 'REGISTER' && !isset($this->_countries[$record[3]]) ){
			echo 'New Country '. $record[3], ' Found', PHP_EOL;

			$countryInsert = $this->runSql($this->_recordTypeSql['COUNTRY'],[$record[3]]);
			
			if($countryInsert['status']){
				echo 'New Country ', $record[3], ' Added ID(', $countryInsert['message'], ')', PHP_EOL;
				$this->_countries[$record[3]] = $countryInsert['message'];
			}else{
				$ok = false;
			}

			$countrySql = null;//clean up
			$countryParams = null;
			$countryInsert = null;

		} elseif ($record['1'] == 'REGISTER') {
			echo 'Country ', $record[3], ' Exists!', PHP_EOL;
		}

		//if there are no errors, process submitted record
		if($ok){

			$update;
			$newInsert;
			$params;

			switch($record[1]){

				case 'REGISTER' :
					$this->_register_inserts['total']++;
					$this->_register_inserts['records'][] = [
						'user_id' => $record[2],
						'created' => date('Y-m-d H:i:s',strtotime($record[0])),
						'country_id' => $this->_countries[$record[3]],
						'ip_address' => rtrim($record[4])
					];
					$this->insertSQL('REGISTER');

					break;

				case 'UPLOAD' :
					$this->_upload_inserts['total']++;
					$this->_upload_inserts['records'][] = [
						'video_id' => $record[3],
						'upload_date' => date('Y-m-d H:i:s',strtotime($record[0])),
						'user_id' => $record[2]
					];
					$this->insertSQL('UPLOAD');

					break;

				case 'WATCH' :
					$this->_watch_inserts['total']++;
					$this->_watch_inserts['records'][] = [
						'video_id' => $record[3],
						'watch_date' => date('Y-m-d H:i:s',strtotime($record[0])),
						'user_id' => $record[2]
					];
					$this->insertSQL('WATCH');

					break;

				case 'LIKE' :
					$this->_like_inserts['total']++;
					$this->_like_inserts['records'][] = [
						'video_id' => $record[3],
						'like_date' => date('Y-m-d H:i:s',strtotime($record[0])),
						'user_id' => $record[2]
					];
					$this->insertSQL('LIKE');

					break;

			}
			$update = null;
			$newInsert = null;
			$params = null;
		}

		$record = null;
	}

	/**
	 * create the database if it doesnt exist and tables
	 * create data base if it doesn't exist and the required tables
	 */
	final private function createDatabase(){

		$create_sql =	"CREATE DATABASE IF NOT EXISTS ".self::DBNAME.";".
						"USE ".self::DBNAME.";".
						"DROP TABLE IF EXISTS countries;".
						"CREATE TABLE countries (".
  						"country_id int(10) unsigned NOT NULL AUTO_INCREMENT,".
  						"country_code varchar(2) DEFAULT NULL,".
						"PRIMARY KEY (country_id),".
						"UNIQUE KEY country_code_UNIQUE (country_code)".
						") ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;".
						"DROP TABLE IF EXISTS users;".
						"CREATE TABLE users (".
						"user_id int(11) unsigned NOT NULL AUTO_INCREMENT,".
						"created datetime DEFAULT NULL,".
						"country_id smallint(3) unsigned DEFAULT '0',".
						"ip_address varchar(15) DEFAULT '000.000.000.000',".
						"total_liked int(10) unsigned DEFAULT '0',".
						"total_watched int(10) unsigned DEFAULT '0',".
						"total_uploaded int(10) unsigned DEFAULT '0',".
						"PRIMARY KEY (user_id),".
						"KEY users_country_idx (country_id)".
						") ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;".
						"DROP TABLE IF EXISTS videos;".
						"CREATE TABLE videos (".
						"video_id int(10) unsigned NOT NULL AUTO_INCREMENT,".
						"upload_date datetime DEFAULT NULL,".
						"user_id int(10) unsigned DEFAULT '0',".
						"likes mediumint(8) unsigned DEFAULT '0',".
						"watched mediumint(8) unsigned DEFAULT '0',".
						"PRIMARY KEY (video_id),".
						"KEY vid_uid_idx (user_id),".
						"KEY vid_likes_idx (likes)".
						") ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;".
						"DROP TABLE IF EXISTS videos_likes_log;".
						"CREATE TABLE videos_likes_log (".
						"vll_id int(10) unsigned NOT NULL AUTO_INCREMENT,".
						"video_id int(10) unsigned DEFAULT '0',".
						"user_id int(10) unsigned DEFAULT '0',".
						"like_date datetime DEFAULT NULL,".
						"PRIMARY KEY (vll_id),".
						"KEY vll_vid_idx (video_id),".
						"KEY vll_uid_idx (user_id)".
						") ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;".
						"DROP TABLE IF EXISTS videos_watch_log;".
						"CREATE TABLE videos_watch_log (".
						"vwl_id int(10) unsigned NOT NULL AUTO_INCREMENT,".
						"video_id int(10) unsigned DEFAULT '0',".
						"user_id int(10) unsigned DEFAULT '0',".
						"watch_date datetime DEFAULT NULL,".
						"PRIMARY KEY (vwl_id),".
						"KEY vwl_vid_idx (video_id),".
						"KEY vwl_uid_idx (user_id)".
						") ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;".
						"DROP TABLE IF EXISTS most;".
						"CREATE TABLE most (".
  						"most_id int(11) unsigned NOT NULL AUTO_INCREMENT,".
  						"type varchar(45) DEFAULT NULL,".
  						"id int(11) unsigned DEFAULT '0',".
						"sortorder tinyint(4) unsigned DEFAULT '0',".
						"PRIMARY KEY (most_id),".
						"KEY type_idx (type)".
						") ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;";
		$create = $this->runSql($create_sql);
		if($create['status']){
			echo LINEBREAK.'Database '.self::DBNAME.' Created!', LINEBREAK;
		}else{
			echo LINEBREAK.$create['message'], LINEBREAK;
		}

		$this->_dbh->exec($create_sql);
	}
 	/**
 	 * Initiates Script
 	 */
	final private function init(){

		$this->createDatabase();


		$handle = fopen(DUMPFILE, 'rb');

		if ($handle) {
			//lets read each line
		    while (($data = fgets($handle, 600)) !== FALSE) {
		        echo $this->_line.': ',$data, PHP_EOL;
		        /**
		         * lets get the second item and determine what type of actions this was
		         * REGISTER | UPLOAD | WATCH | LIKE
		         */
		        $this->processRecord($data);

				$data = null;//clear memory

				//stop execution if a limit was set
				if($this->_line==self::MAXREAD)
					break;

				$this->_line++;
			}
			//lets process any unprocessed chunks
			$this->insertSQL('REGISTER',true);
			$this->insertSQL('UPLOAD',true);
			$this->insertSQL('WATCH',true);
			$this->insertSQL('LIKE',true);

		    if (!feof($handle)) {
		        echo 'Data Dump ended before the End of File('.self::MAXREAD.' rows processed).',PHP_EOL;
		    }
		    fclose($handle);
		}
		
		echo PHP_EOL,PHP_EOL,LINEBREAK,
			'Data Dump Complete', LINEBREAK,
			'Total Records Added: ', $this->_successful, LINEBREAK.
			'Total Errors: ', $this->_errors, LINEBREAK.
			'Total Process Time: ', (microtime(true)-$this->_starttime)." Seconds",LINEBREAK;

		//updating all video and user totals
		echo PHP_EOL.LINEBREAK,'Updating User Totals...', PHP_EOL;
		
		$updateUsersSql =	'UPDATE 
								users u
							LEFT JOIN
								(SELECT user_id ups_id, count(*) ups_total FROM videos GROUP BY ups_id) ups
								ON u.user_id = ups.ups_id
							LEFT JOIN
								(SELECT user_id likes_id, count(*) likes_total FROM videos_likes_log GROUP BY likes_id) likes
								ON u.user_id = likes.likes_id
							LEFT JOIN
								(SELECT user_id watch_id, count(*) watch_total FROM videos_watch_log GROUP BY watch_id) watched
								ON u.user_id = watched.watch_id
							SET
								u.total_uploaded = coalesce(ups_total,0),
								u.total_liked = coalesce(likes_total,0),
								u.total_watched = coalesce(watch_total,0)';
		
		$updatedUsers = $this->_dbh->exec($updateUsersSql);
		echo $updatedUsers,' Users Updated.',LINEBREAK;

		echo PHP_EOL.LINEBREAK,'Updating Videos Totals...', PHP_EOL;

		$updateVidsSql =	'UPDATE 
								videos v
							LEFT JOIN
								(SELECT video_id likes_id, count(*) likes_total FROM videos_likes_log GROUP BY likes_id) lt
								ON v.video_id = lt.likes_id
							LEFT JOIN
								(SELECT video_id watch_id, count(*) watch_total FROM videos_watch_log GROUP BY watch_id) wt
								ON v.video_id = wt.watch_id
							SET
								v.likes = coalesce(likes_total,0),
								v.watched = coalesce(watch_total,0)';

		$updatedVids = $this->_dbh->exec($updateVidsSql);
		echo $updatedVids,' Videos Updated.',LINEBREAK;

		echo PHP_EOL.LINEBREAK,'Updating Most(Top 5)...', PHP_EOL;

		$getTopSql = 'SELECT * FROM videos ORDER BY watched DESC LIMIT 5';
		//get top 5 videos from user table...no error handling for now i can add it later		
		$STH = $this->_dbh->prepare($getTopSql);
		$STH->execute();
		$i=1;
		while($row = $STH->fetch(PDO::FETCH_ASSOC)){
			//enter each record into the most table with the sort order number
			$id = $row['video_id'];
			$addTopSQL = "INSERT INTO most (type,id,sortorder) VALUES ('mwv',{$id},{$i})";
			$this->_dbh->exec($addTopSQL);
			$i++;
		}

		//adding top 5 in most table for more efficient access to data
		echo LINEBREAK,'Data Update Complete', LINEBREAK;
		//close DB Connection
		$this->_dbh = null;
	}

}