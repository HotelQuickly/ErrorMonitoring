<?php

namespace HQ\Api;

use Nette;
use BaseLibrary\Api\BaseRequestFactory;
use HQ\Api\Request;

/**
 * RequestFactory
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */
class RequestFactory extends BaseRequestFactory {

	/** @var array - target host names (http://ihub.hotelquickly.com) */
	private $apiTargetHosts = array();

	public function __construct($apiTargetHosts = array())
	{
		$this->apiTargetHosts = $apiTargetHosts;
	}


	public function createExampleRequest()
	{
		$request = new Request\ExampleRequest($this->apiTargetHosts);
		$request = $this->addCommonParams($request);

		return $request;
	}

}