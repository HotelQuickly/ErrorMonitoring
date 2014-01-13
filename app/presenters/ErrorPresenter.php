<?php

use Nette\Diagnostics\Debugger,
	Nette\Application as NA,
	HotelQuickly as HQ;



/**
 * Error presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ErrorPresenter extends \FrontendModule\BasePresenter
{

	/**
	 * @param  Exception
	 * @return void
	 */
	public function renderDefault($exception)
	{
		if ($exception) {
			$data = array(
				'error_msg' => $exception->getMessage(),
				'upd_process_id' => 'ErrorPresenter::renderDefault()',
			);
			if (isset($this->lastLogItem) && is_object($this->lastLogItem) ) {
				$this->logger->updateLogVisit($this->lastLogItem->id, $data);
			}
		}

		if ($this->isAjax()) { // AJAX request? Just note this error in payload.
			$this->payload->error = TRUE;
			$this->terminate();

		} elseif ($exception instanceof NA\BadRequestException || $exception instanceof HQ\UnauthorizedAccessException) {
			$code = $exception->getCode();
			if($exception instanceof HQ\UnauthorizedAccessException){
				// Unathorized access in admin
				$this->setView("admin403");
			} else {
				// load template 403.latte or 404.latte or ... 4xx.latte
				$this->setView(in_array($code, array(403, 404, 405, 410, 500)) ? $code : '4xx');
			}
			// log to access.log
			Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');

		} else {
			$this->setView('500'); // load template 500.latte
			$this->logger->logError($exception, 'exception', 'ErrorPresenter::LOG_EXCEPTION');
		}
	}

}
