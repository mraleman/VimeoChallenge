<?php namespace Vimeochallenge\Source;

/**
 * UserModel will return relevant USER information from the DB
 * getProfile() - retrieves User Profile that includes summary
 * getUploads() - returns list of User's video uploads including video url and upload total
 * getWatched() - returns list of User's watched videos including video url and watched total
 * getLiked() - returns list of User's liked videos including video url and liked total
 * postUser() - will create a new user, returns 201 code with user profile(getProfile()) on success;
 * deleteUser() - will delete user, returns 404 server error on success
 * patchUser() - will update specified resource - returns 200 code with user profile(getProfile) with updates
 */
class UserModel extends BaseTemplate
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
	final public function getProfile($id){
		if($this->_response['status']){
			$rslt = $this->getResults("SELECT * FROM users u INNER JOIN countries c ON u.country_id = c.country_id WHERE user_id = :id",[':id'=>$id]);
			if($rslt['status'] && !empty($rslt['result'])){
				//only use first result
				//format data so that it outputs the way we want it
				$this->_response['data'] = [
					'userId' => $rslt['result'][0]['user_id'],
					'country' => [
						'code' => $rslt['result'][0]['country_code'],
						'href' =>  self::URL.'/country/'.$rslt['result'][0]['country_code']
					],
					'uploads' => [
						'total' => $rslt['result'][0]['total_uploaded'],
						'href' =>  self::URL.'/user/'.$id.'/uploads'
					],
					'watched' => [
						'total' => $rslt['result'][0]['total_watched'],
						'href' =>  self::URL.'/user/'.$id.'/watched'
					],
					'liked' => [
						'total' => $rslt['result'][0]['total_liked'],
						'href' =>  self::URL.'/user/'.$id.'/liked'
					]
				];
			//the sql was good but returned no results
			} elseif ($rslt['status'] && empty($rslt['result'])){
				$this->setError('Invalid User ID');
			} else {
				$this->setError($rslt['reason']);
			}
			$rslt = null;
		}
	}
	final public function getUploads(int $id,int $page=1,int $limit=20){
		if($this->_response['status']){
			$rslt = $this->getResults("SELECT u.user_id uid, u.total_uploaded, v.* FROM users u LEFT JOIN videos v ON v.user_id = u.user_id WHERE u.user_id = :id LIMIT 0,{$limit}",[':id'=>$id]);
			//we know that at the very least we should get back a row with the user info if it's valid
			if($rslt['status'] && !empty($rslt['result'])){
				$list = [];
				$rows = $rslt['result'];
				for($i=0;$i<count($rows);$i++){
					if(!is_null($rows[$i]['video_id'])){
						$list[] = [
							'videoId' => $rows[$i]['video_id'],
							'uploadDate' => date(DATE_ISO8601, strtotime($rows[$i]['upload_date'])),
							'watchTotal' => $rows[$i]['watched'],
							'likeTotal' => $rows[$i]['likes'],
							'href' => self::URL.'/video/'.$rows[$i]['video_id'],
						];
					}
				}
				//format data so that it outputs the way we want it
				$this->_response['data'] = [
					'userId' => $rows[0]['uid'],
					'total' => $rows[0]['total_uploaded'],
					'limit' => $limit,
					'result' => $list,
					///LOGIC NOT IN PLACE TO HANDLE PAGINATION YET
					'links' => [
						'prevPage' => self::URL.'/user/'.$id.'/uploads/page/1',
						'self' => self::URL.'/user/'.$id.'/uploads/page/2',
						'nextPage' => self::URL.'/user/'.$id.'/uploads/page/3'
					]
				];
				$rows = null;
				$list = null;
			//the sql was good but returned no results
			} elseif ($rslt['status'] && empty($rslt['result'])){
				$this->setError('Invalid User ID');
			} else {
				$this->setError($rslt['reason']);
			}
			$rslt = null;
		}
	}
	final public function getWatched(int $id,int $page=1,int $limit=20){
		if($this->_response['status']){
			$rslt = $this->getResults("SELECT u.user_id uid, u.total_watched, vwl.*, CASE WHEN vll.vll_id IS NOT NULL THEN 1 ELSE 0 END AS liked,
	vll.like_date FROM users u, videos_watch_log vwl LEFT JOIN videos_likes_log vll ON (vll.video_id = vwl.video_id AND vll.user_id = vwl.user_id) WHERE u.user_id = vwl.user_id AND vwl.user_id = :id ORDER BY vwl.watch_date DESC LIMIT 0,{$limit}",[':id'=>$id]);
			//we know that at the very least we should get back a row with the user info if it's valid
			if($rslt['status'] && !empty($rslt['result'])){
				$list = [];
				$rows = $rslt['result'];
				for($i=0;$i<count($rows);$i++){
					if(!is_null($rows[$i]['video_id'])){
						$list[] = [
							'videoId' => $rows[$i]['video_id'],
							'watchedDate' => date(DATE_ISO8601, strtotime($rows[$i]['watch_date'])),
							'liked' => boolval($rows[$i]['liked']),
							'likedDate' => is_null($rows[$i]['like_date'])?'':date(DATE_ISO8601, strtotime($rows[$i]['like_date'])),
							'href' => self::URL.'/video/'.$rows[$i]['video_id'],
						];
					}
				}

				//format data so that it outputs the way we want it
				$this->_response['data'] = [
					'userId' => $rows[0]['uid'],
					'total' => $rows[0]['total_watched'],
					'limit' => $limit,
					'result' => $list,
					///LOGIC NOT IN PLACE TO HANDLE PAGINATION YET
					'links' => [
						'prevPage' => self::URL.'/user/'.$id.'/watched/page/1',
						'self' => self::URL.'/user/'.$id.'/watched/page/2',
						'nextPage' => self::URL.'/user/'.$id.'/watched/page/3'
					]
				];
				$rows = null;
				$list = null;
			//the sql was good but returned no results
			} elseif ($rslt['status'] && empty($rslt['result'])){
				$this->setError('Invalid User ID');
			} else {
				$this->setError($rslt['reason']);
			}
			$rslt = null;
		}
	}
	final public function getLiked(int $id,int $page=1,int $limit=20){
		if($this->_response['status']){
			$rslt = $this->getResults("SELECT u.user_id uid, u.total_liked, vll.* FROM users u, videos_likes_log vll WHERE u.user_id = vll.user_id AND vll.user_id = :id ORDER BY vll.like_date DESC LIMIT 0,{$limit}",[':id'=>$id]);
			//we know that at the very least we should get back a row with the user info if it's valid
			if($rslt['status'] && !empty($rslt['result'])){
				$list = [];
				$rows = $rslt['result'];
				for($i=0;$i<count($rows);$i++){
					if(!is_null($rows[$i]['video_id'])){
						$list[] = [
							'videoId' => $rows[$i]['video_id'],
							'likedDate' => is_null($rows[$i]['like_date'])?'':date(DATE_ISO8601, strtotime($rows[$i]['like_date'])),
							'href' => self::URL.'/video/'.$rows[$i]['video_id'],
						];
					}
				}

				//format data so that it outputs the way we want it
				$this->_response['data'] = [
					'userId' => $rows[0]['uid'],
					'total' => $rows[0]['total_liked'],
					'limit' => $limit,
					'result' => $list,
					///LOGIC NOT IN PLACE TO HANDLE PAGINATION YET
					'links' => [
						'prevPage' => self::URL.'/user/'.$id.'/liked/page/1',
						'self' => self::URL.'/user/'.$id.'/liked/page/2',
						'nextPage' => self::URL.'/user/'.$id.'/liked/page/3'
					]
				];
				$rows = null;
				$list = null;
			//the sql was good but returned no results
			} elseif ($rslt['status'] && empty($rslt['result'])){
				$this->setError('Invalid User ID');
			} else {
				$this->setError($rslt['reason']);
			}
			$rslt = null;
		}
	}
	final public function postUser($data){
		if($this->_response['status']){         
			/**
			 * Before trying to insert the new user,
			 * we have to make sure that the required parameters are included
			 */
			$req_params = ['country','ipAddress'];

			if(isset($data['country'],$data['ipAddress'])){
				/**
				 * let's get country id from countries table
				 * Ideally, we should store this cross-user data in an Application State(not supported by php)
				 * There are different ways you can emulate this with PHP but that would be for some other time
				 * For now we will query this info
				 */
				$country = $this->getResults("SELECT country_id FROM countries WHERE country_code = :code",[':code'=>$data['country']]);

				if($country['status']){
					$created = gmdate("Y-m-d H:i:s");//create UTC timestamp
					$cid = $country['result'][0]['country_id'];
					$ip = $data['ipAddress'];
					$insert_sql = "INSERT INTO users (created, country_id, ip_address) VALUES ('{$created}','{$cid}','{$ip}')";

					$rslt = $this->getResults($insert_sql);

					if($rslt['status']){
						$this->_response = $rslt;
					}else{
						$this->setError($rslt['reason']);
					}
				}else{
					$this->setError('Invalid Country');
				}

			}else{
				$this->setError('Missing Required Parameter');
			}
		}
	}
	final public function deleteUser($id){
		if($this->_response['status']){

			$rslt = $this->getResults("DELETE FROM users WHERE user_id = :uid",[':uid'=>$id]);

			if($rslt['status']){
				$this->_response = $rslt;
			}else{
				$this->setError($rslt['reason']);
			}
		}
	}
	final public function patchUser($id,$params){
		if($this->_response['status']){
			//these are our valid params and maps to corresponding table columns
			$validParams = ['addlike'=>'total_liked','addwatch'=>'total_watched','addupload'=>'total_uploaded'];
			$sets = [];

			/**
			 * Loop through each param sent and check if it is valid
			 * Also check to make sure that the values are bool
			 **/
			foreach($params as $k => $v){
				$k=strtolower($k);
				if(!array_key_exists($k,$validParams)){
					$this->setError('Invalid Parameter Set');
					return false;
				}elseif(!is_bool($v)){
					$this->setError('Invalid Parameter Value Set');
					return false;
				}else{
					$sets[] = "{$validParams[$k]} = {$validParams[$k]}".($v ? "+1":"-1");
				}
			}
			$rslt = $this->getResults("UPDATE users SET ".implode(", ",$sets)." WHERE user_id = :uid",[':uid'=>$id]);

			if($rslt['status']){
				$this->_response = $rslt;
			}else{
				$this->setError($rslt['reason']);
			}

		}
	}
}