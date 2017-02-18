<?php

require_once '../source/lib/BaseTemplate.php';
require_once '../source/lib/Database.php';

/**
 * CountryModel will return relevant COUNTRY information from the DB
 * getUsers() - returns list of Users based on the Country Provided including User's Profile url
 * Extra methods can be added to provide country Profile|VIDEOS BY COUNTRY etc...
 */
class CountryModel extends BaseTemplate
{
	/**
	 * Connect with Database and establish a DB Handler to use
	 */
	final public function __construct(){
		//lets start by connecting to the db
		$conn = new Database;
		$this->_dbh = $conn->connect();
		if(!$this->_dbh){
			$db_response = $conn->getResponse();
			$this->setError($db_response['reason']);
			$this->_dbh = null;
		};
		$conn = null;
	}
	final public function getUsers($id, $page=1,$limit=20){
		if($this->_response['status']){
			$rslt = $this->getResults("SELECT u.user_id, c.country_code FROM users u, countries c WHERE u.country_id = c.country_id AND c.country_code = :id LIMIT 0,{$limit}",[':id'=>$id]);
			if($rslt['status'] && !empty($rslt['result'])){
				$list = [];
				$rows = $rslt['result'];
				for($i=0;$i<count($rows);$i++){
					$list[] = [
						'userId' => $rows[$i]['user_id'],
						'href' => self::URL.'/user/'.$rows[$i]['user_id'].'/profile'
					];
				}
				//format data so that it outputs the way we want it
				$this->_response['data'] = [
					'countryCode' => $id,
					//'total' => '',//not implemented yet
					'limit' => $limit,
					'result' => $list,
					'prevPage' => self::URL.'/country/'.$id.'/users/page/1',///LOGIC NOT IN PLACE TO HANDLE PAGINATION YET
					'nextPage' => self::URL.'/user/'.$id.'/users/page/3'///LOGIC NOT IN PLACE TO HANDLE PAGINATION YET
				];
				$rows=null;
				$list=null;
			//the sql was good but returned no results
			} elseif ($rslt['status'] && empty($rslt['result'])){
				$this->setError('Invalid Country Code');
			} else {
				$this->setError($rslt['reason']);
			}
			$rslt = null;
		}
	}
}