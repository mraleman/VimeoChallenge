<?php namespace Vimeochallenge\Source;

/**
* Base Template used by all Controllers and Models.
* Sets URL Constant from this single-point, required parameters and methods
*/
use \PDO;

abstract class BaseTemplate
{
	
	const URL = API_ROOT;

	protected $_dbh;
	protected $_response = ['status'=>true];
	protected $_uri;
	/**
	 * This will save the URI for further page validation
	 */
	final public function setUri($uri){
		$this->_uri = $uri;
	}
	/**
	 * Use this method to submit a SELECT statement
	 * First param should be a valid SQL Statement
	 * This will return an array with status and result
	 * If status is false, Reason will be sent with error
	 */
	final protected function getResults(string $sql,array $params=NULL){
		$obj = ['status'=>true,'result'=>[]];

		$this->_dbh->beginTransaction();

		try {

			$STH = $this->_dbh->prepare($sql);
			$STH->execute($params);

			//get the Statement Type
			$sqlType = explode(' ',$sql,2);
			//get insert id if this was a new insert statement
			if($sqlType[0] == "INSERT"){
				$insertId = $this->_dbh->lastinsertid();
				$obj['insertId'] = $insertId;			
			//this will return any results if available
			}elseif($sqlType[0] == "SELECT"){
				while($row = $STH->fetch(PDO::FETCH_ASSOC)){
					$obj['result'][] = $row;
				}
			//return rowCount to know how many rows were updated
			}elseif($sqlType[0] == "UPDATE"){
				$obj['rows_affected'] = $STH->rowCount();
			}

			//this returns last insertid if available
			$this->_dbh->commit();

		} catch (PDOException  $e) {
			$this->_dbh->rollBack();
			$obj['status'] = false;
			$obj['reason'] = 'Database Error: '.$e->getMessage();
		}

		return $obj;
	}
	/**
	 * call this method to set an error message
	 * the status will also be set to false
	 */
	final public function setError($reason){
		$this->_response['status'] = false;
		$this->_response['reason'] = $reason;
	}
	final public function getResponse(){
		return $this->_response;
	}
	final public function __destruct(){
		$this->_dbh = null;
	}
}