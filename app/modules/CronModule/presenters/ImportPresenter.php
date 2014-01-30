<?php

namespace CronModule;

class ImportPresenter extends \BasePresenter {

	/**
	 * @autowire
	 * @var \HQ\ErrorMonitorinq\ImportService
	 */
	protected $importService;

	public function actionImportFiles() {
		set_time_limit(0);
		$this->importService->import();
		$this->terminate();
	}
	
}
