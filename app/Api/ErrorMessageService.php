<?php

namespace HQ\Api;

class ErrorMessageService extends \Nette\Object
{
	public function getErrorResponse($errorId, $param1 = null, $param2 = null, $param3 = null, $param4 = null) {
		$ret = array();

		switch($errorId) {
			case 'INVALID_METHOD':
				$ret = array(
					'code' => 101,
					'message' => 'Invalid use of API. Use ' . $param1 .' method instead of ' . $param2 . '.',
				);
				break;
			case 'REQUIRED_PARAM':
				$ret = array(
					'code' => 102,
					'message' => 'Required parameter ' . $param1 . ' is missing.',
				);
				break;
			case 'INVALID_VALUE':
				$ret = array(
					'code' => 103,
					'message' => 'Attribute ' . $param1 . 'contains invalid value.',
					'description' => 'This attribute does not allow the value you try to save. Please check documentation for the list of valid values.',
				);
				break;
			case 'WRONG_METHOD_USED':
				$ret = array(
					'code' => 104,
					'message' => 'Wrong method used.',
					'description' => 'Please check method in your API call. GET/POST/DELETE',
				);
				break;
			case 'OBJECT_DOES_NOT_EXIST':
				$ret = array(
					'code' => 105,
					'message' => 'This object does not exist. It could have been deleted.',
					'description' => 'The object you try to select does not exist anymore, you might have deleted it.',
				);
				break;
		}

		return $ret;
	}
}
