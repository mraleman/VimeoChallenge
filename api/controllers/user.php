<?php

/**
 * User Class Controller that will get data from the User Model with corresponding REQUEST METHOD
 */
require_once '../source/lib/BaseTemplate.php';
require_once '../source/lib/BaseController.php';
require 'models/UserModel.php';

class User extends BaseTemplate implements BaseController
{
	final public function getData($params){

		$uModel = new UserModel;
		$val = $params[1] ?? false;
		$getMethod = isset($params[2]) && !is_null($params[2])?'get'.$params[2]:'getProfile';

		if(!$val){
			$this->setError('Missing ID');
		}elseif(!method_exists($uModel, $getMethod)){
			$this->setError('Invalid Method');
		}else{
			$uModel->$getMethod($val,1);
			$this->_response = $uModel->getResponse();
		}
		$uModel = null;
	}
	final public function postData($params){
		//if only 1 uri param than it must be /user
		if(count($this->_uri)==1){
			$uModel = new UserModel;
			$uModel->postUser($params);

			$this->_response = $uModel->getResponse();

			//if added show newly created user object
			if($this->_response['status']){
				http_response_code(201);
				echo file_get_contents(self::URL.'/user/'.$this->_response['insertId']);
				$uModel = null;
				exit;
			}
		}else{
			$this->setError('Invalid Request');
		}
	}
	final public function deleteData($params){
		/**
		 * Ideally you may want use the params to provide information
		 * that would help proper validation of user
		 */
		//if only 2 uri param than it must be /user/{something}
		if(count($this->_uri)==2){
			$uModel = new UserModel;

			$uModel->deleteUser($this->_uri[1]);

			$this->_response = $uModel->getResponse();

			//if added show newly created user object
			if($this->_response['status']){
				/**
				 * send 404 server error upon successful deletion
				 * or we can handle it differently if desired
				 */
				http_response_code(404);
				$uModel = null;
				exit;
			}

		}else{
			$this->setError('Invalid Request');
		}
	}
	final public function patchData($params){
		/**
		 * Ideally you may want use the params to include information
		 * that would help proper validation of user making changes
		 */
		if(count($this->_uri)==2 && !empty($params)){
			$uModel = new UserModel;

			$uModel->patchUser($this->_uri[1],$params);
			
			$this->_response = $uModel->getResponse();

			//if updated show updated user object
			if($this->_response['status']){
				echo file_get_contents(self::URL.'/user/'.$this->_uri[1]);
				$uModel = null;
				exit;
			}
		}else{
			$this->setError('Invalid Request');
		}
	}
}