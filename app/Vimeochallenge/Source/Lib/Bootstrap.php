<?php namespace Vimeochallenge\Source;

/**
 * This will initiate the relevant controllers for the API and Captures all Request Methods
 */

class Bootstrap
{
	
	public function __construct(){
		//start by getting the request method used
		$_RM = $_SERVER['REQUEST_METHOD'];
		$content = null;
		/**
		 * if the request was a PATCH or DELETE request
		 * the request should be in JSON
		 */
		if($_RM == 'PATCH' || $_RM == 'DELETE' || $_RM == 'POST'){
			$request_content = file_get_contents("php://input");
			if($request_content!=false){
				$content = json_decode($request_content,true);
			}
		}

		$params = isset($_GET['url'])?explode('/',$_GET['url']):[''];
		/**
		 * just listing all available controllers/classes for this api.
		 * an error will be returned if it does not exist
		 */
		$file = dirname(__FILE__).'/../Controllers/'.ucwords($params[0]).'.php';
		echo $file;
		if (file_exists($file)){

			//require $file;
			//Vimeochallenge\Source\Controllers\.
			$className = 'Vimeochallenge\\Source\\'.ucwords($params[0]);
			$controller = new $className;

			//set the proper class method to be used based on the REQUEST_METHOD
			switch($_RM){
				case 'GET' :
					$arg = $params;
					$method = 'getData';
					break;
				case 'POST' :
					$arg = $content;
					$method = 'postData';
					break;
				case 'DELETE' :
					$arg = $content;
					$method = 'deleteData';
					break;
				case 'PATCH' :
					$arg = $content;
					$method = 'patchData';
					break;
				default:
					$method = 'unknown';
			}
			/**
			 * use the method if it exists within the class
			 * send argument as well
			 */
			if (method_exists($controller, $method)){
				$controller->setUri($params);
				$controller->$method($arg);
			} else {
				$controller->setError('Invalid Method ('.$method.')');
			}
			/**
			 * We will always get the response from our controller and return it in JSON
			 * ....this can be changed to return in a different format if required.
			 */
			$response = $controller->getResponse();

		} else {
			$response = ['status'=>false,'reason'=>'Invalid Request a'];
		}

		/**
		 * Output JSON to client as final view.
		 * This can be changed to handle different Output Formats
		 */
		echo json_encode($response);
	}
}