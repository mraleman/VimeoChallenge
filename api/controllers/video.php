<?php
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);//used for debugging
/**
 * User Class Controller that will get data from the User Model with corresponding REQUEST METHOD
 */
require_once '../source/lib/BaseTemplate.php';
require_once '../source/lib/BaseController.php';
require_once 'models/VideoModel.php';

class Video extends BaseTemplate implements BaseController
{

	final public function getData($params){

		$vModel = new VideoModel;
		$val = $params[1] ?? false;
		$getMethod = isset($params[2]) && !is_null($params[2])?'get'.$params[2]:'getProfile';

		if(!$val){
			$this->setError('Missing ID');
		}elseif(!$getMethod || !method_exists($vModel, $getMethod)){
			$this->setError('Invalid Method');
		}else{
			$vModel->$getMethod($val,20);
			$this->_response = $vModel->getResponse();
		}
		$vModel = null;

	}
	final public function postData($params){
		$this->setError('Invalid Request');
	}
	final public function deleteData($params){
		$this->setError('Invalid Request');
	}
	final public function patchData($params){
		$this->setError('Invalid Request');
	}
}