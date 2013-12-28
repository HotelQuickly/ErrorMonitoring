<?php

/**
 * Description of Homepage
 *
 */
class ApiExamplePresenter extends BasePresenter {

	/** @autowire @var \HQ\Api\RequestFactory */
	protected $requestFactory;

	public function renderDefault() {
		$data = $this->requestFactory->createExampleRequest()
			->addParams(array('cityName' => 'Bangkok')) // add GET parameters
			->makeRequest();

		// when some data are added, request will be automatically send as POST
		$data2 = $this->requestFactory->createExampleRequest()
			->addParams(array('cityName' => 'Bangkok')) // add GET parameters
			->setData(array('user_id' => 1)) // set POST parameters
			->makeRequest();

		$stop();
	}

}