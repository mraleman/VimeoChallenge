<?php namespace Vimeochallenge\Source;

/**
 * User Class Controller that will get data from the User Model.
 */
class Video extends BaseTemplate implements BaseController
{
    final public function getData($params)
    {
        $vModel = new VideoModel;
        $val = $params[1] ?? false;
        if (isset($params[2]) && !is_null($params[2])) {
            $getMethod = 'get'.$params[2];
        } else {
            $getMethod = 'getProfile';
        }

        if (!$val) {
            $this->setError('Missing ID');
        } elseif (!$getMethod || !method_exists($vModel, $getMethod)) {
            $this->setError('Invalid Method');
        } else {
            $vModel->$getMethod($val,20);
            $this->_response = $vModel->getResponse();
        }
        $vModel = null;
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
