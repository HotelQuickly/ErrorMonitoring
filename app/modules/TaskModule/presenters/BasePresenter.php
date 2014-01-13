<?php

namespace TaskModule;

/**
 * @author Jetsada Machom
 */
class BasePresenter extends \BasePresenter
{
	protected $skypeLogger;

	protected $param;

	/**
	 * DI for SkypeLogger
	 * @param  \Skype\SkypeLogger $skypeLogger
	 */
	public function injectSkypeLogger(\Skype\SkypeLogger $logger)
	{
		$this->skypeLogger = $logger;
	}

	public function startup() {
		parent::startup();

		ini_set('memory_limit', '512M');

		if (extension_loaded('newrelic')) {
			newrelic_background_job();
		}

		$param = $this->presenter->context->httpRequest->getPost('param');
		if(!empty($param)) {
			$this->param = json_decode($param, true);
		} else {
			$this->param = null;
		}
	}

	public function prepareResponse($success=false, $message=null) {
		$response = array('status' => $success, 'message' => $message);
		$this->sendResponse(new \Nette\Application\Responses\JsonResponse($response));
		$this->terminate();
	}

}
