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

	public function actionImportProjects() {
		set_time_limit(0);
		$this->importService->importProjects();
		$this->terminate();
	}
}
