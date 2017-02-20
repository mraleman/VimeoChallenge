<?php namespace Vimeochallenge\Source;

/**
 * This will initiate the relevant controllers for the API
 * and captures all Request Methods.
 */

class Bootstrap
{

    public function __construct()
    {
        //Start by getting the request method used.
        $_RM        = $_SERVER['REQUEST_METHOD'];
        $content    = null;

        /**
         * If the request was a PATCH or DELETE request.
         * The request should be in JSON in this version of the API.
         */
        if($_RM == 'PATCH' || $_RM == 'DELETE' || $_RM == 'POST'){
            $request_content = file_get_contents("php://input");
            if($request_content!=false){
                $content = json_decode($request_content,true);
            }
        }

        $params = isset($_GET['url'])?explode('/',$_GET['url']):[''];

        /**
         * Just listing all available controllers/classes for this api.
         * An error will be returned within the response if it does not exist.
         */
        $rootPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
        $controlDir = DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR;
        $controlPath = $rootPath.$controlDir;
        $fileName = ucwords($params[0]);

        $file = $controlPath.$fileName.'.php';

        if (file_exists($file)) {

            $className = 'Vimeochallenge\\Source\\'.ucwords($params[0]);
            $controller = new $className;

            //Set the proper method to be used based on the REQUEST_METHOD.
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
             * Use the method if it exists within the class.
             * Send argument within the contorller's method.
             */
            if (method_exists($controller, $method)) {
                $controller->setUri($params);
                $controller->$method($arg);
            } else {
                $controller->setError('Invalid Method ('.$method.')');
            }

            /**
             * Get the response from our controller and return it in JSON.
             * This can be changed to return in a different format if required.
             */
            $response = $controller->getResponse();

        } else {
            $response = ['status'=>false,'reason'=>'Invalid Request a'];
        }

        /**
         * Output JSON to client as final view.
         * This can be changed to handle different Output Formats.
         */
        echo json_encode($response);
    }
}
