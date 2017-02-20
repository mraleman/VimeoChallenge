<?php namespace Vimeochallenge\Source;

/**
 * Setting required methods for all Controllers.
 */
interface BaseController
{
    public function getData($params);
    public function postData($params);
    public function deleteData($params);
    public function patchData($params);
}
