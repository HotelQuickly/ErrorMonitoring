<?php

namespace CronModule;

class ErrorCollectorPresenter extends BasePresenter {

	/** @autowire
	 * @var \HQ\ErrorCollector\ErrorCollector */
	protected $errorCollector;

	public function actionUploadErrors()
	{
		$exceptionCnt = $this->errorCollector->uploadFiles();
	}

}