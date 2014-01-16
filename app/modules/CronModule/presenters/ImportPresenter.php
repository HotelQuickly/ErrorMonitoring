<?php

namespace CronModule;

class ImportPresenter extends \BasePresenter {

	/**
	 * @autowire
	 * @var \HQ\ErrorMonitorinq\ImportService
	 */
	protected $importService;

	public function actionImportFiles() {
		$this->importService->import();
	}
}
