<?php namespace Vimeochallenge\Source;

/**
 * User Class Controller that will get data from the User Model with corresponding REQUEST METHOD
 */

class Most extends BaseTemplate implements BaseController
{

	final public function getData($params){
		$mModel = new MostModel;
		$getMethod = isset($params[1]) && !is_null($params[1])?'get'.$params[1]:false;

		if(!$getMethod || !method_exists($mModel, $getMethod)){
			$this->setError('Invalid Method');
		}else{
			$mModel->$getMethod();
			$this->_response = $mModel->getResponse();
		}
		$mModel = null;

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