<?php namespace Vimeochallenge\Source;

class Country extends BaseTemplate implements BaseController
{

	final public function getData($params){

		$cModel = new CountryModel;
		$val = $params[1] ?? false;
		$getMethod = isset($params[2]) && !is_null($params[2])?'get'.$params[2]:false;

		if(!$val){
			$this->setError('Missing ID');
		}elseif(!$getMethod || !method_exists($cModel, $getMethod)){
			$this->setError('Invalid Method');
		}else{
			$cModel->$getMethod($val,20);
			$this->_response = $cModel->getResponse();
		}
		$cModel = null;

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