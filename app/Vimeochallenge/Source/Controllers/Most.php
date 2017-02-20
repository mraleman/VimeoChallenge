<?php namespace Vimeochallenge\Source;

/**
 * User Class Controller that will get data from the User Model.
 */
class Most extends BaseTemplate implements BaseController
{

    final public function getData($params)
    {
        $mModel = new MostModel;
        if (isset($params[1]) && !is_null($params[1])) {
            $getMethod = 'get'.$params[1];
        } else {
            $getMethod = false;
        }

        if (!$getMethod || !method_exists($mModel, $getMethod)) {
            $this->setError('Invalid Method');
        } else {
            $mModel->$getMethod();
            $this->_response = $mModel->getResponse();
        }
        $mModel = null;
    }

    final public function postData($params)
    {
        $this->setError('Invalid Request');
    }

    final public function deleteData($params)
    {
        $this->setError('Invalid Request');
    }

    final public function patchData($params)
    {
        $this->setError('Invalid Request');
    }
}
