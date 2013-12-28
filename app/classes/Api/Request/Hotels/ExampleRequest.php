<?php

namespace HQ\Api\Request;

use Nette;
use BaseLibrary\Api\BaseRequest;

/**
 * ExampleRequest
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */
class ExampleRequest extends BaseRequest {

	public function __construct($targetHosts = array())
	{
		$targetHost = (array_key_exists(self::BACKEND, $targetHosts) ? $targetHosts[self::BACKEND] : self::TARGET_HOST_BACKEND);

		$this->setTargetHost($targetHost);
		$this->setTargetUrl('/cities/tax');

		// you can specify mandatory parameters
		$this->setMandatoryParams(array('cityName'));
	}


	public function makeRequest()
	{
		$response = $this->sendRequest();
		$this->checkResponse($response);
		return $response['response'];
	}
}