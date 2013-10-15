<?php

namespace ApiModule;

abstract class BasePresenter extends \BasePresenter
{

	const VALID_RESPONSE_CODE = 200;
	const VALID_RESPONSE_STATUS = 'Success';

	/** @autowire @var \HQ\Logger */
	protected $loggerService;

	/** @autowire @var \HQ\Api\ErrorMessageService */
	protected $errorMessageService;



	public function prepareAndSendErrorResponse($errorId, $param1 = null, $param2 = null)
	{
		$errorResponse = $this->errorMessageService->getErrorResponse($errorId, $param1, $param2);

		// Log Error
		$errorData = array(
			'error_msg' => $errorResponse['message'] . PHP_EOL . (isset($errorResponse['description']) ? $errorResponse['description'] : ''),
			'api_response' => serialize($errorResponse),
			'upd_process_id' => __METHOD__,
		);
		$this->loggerService->updateLogVisit($this->lastLogItem->id, $errorData);
		$this->loggerService->logError("API Error #" . $errorResponse['code'] . " " . $errorId . ": ". $errorData["error_msg"], 'error');

		// Send Response
		$response = array(
			'status' => $errorResponse,
			'response' => null,
		);

		$this->sendResponse(new \Nette\Application\Responses\JsonResponse($response));
	}



	public function prepareAndSendValidResponse($arr) {

		$response = array(
			'status' => array(
				'code' => self::VALID_RESPONSE_CODE,
				'message' => self::VALID_RESPONSE_STATUS,
			),
			'response' => $arr
		);

		// Log Valid Response
		$data = array(
			'api_response' => serialize($response),
			'upd_process_id' => 'BasePresenter::prepareAndSendValidResponse()',
		);
		$this->loggerService->updateLogVisit($this->lastLogItem->id, $data);

		// Send response
		$this->sendResponse(new \Nette\Application\Responses\JsonResponse($response));
	}

}
