<?php

namespace AdminModule;

class ErrorListPresenter extends BasePresenter {

	/**
	 * @autowire
	 * @var \HQ\Model\Entity\ErrorEntity
	 */
	protected $errorEntity;

	/**
	 * @autowire
	 * @var \HQ\Model\Entity\ProjectEntity
	 */
	protected $projectEntity;

	/**
	 * @autowire
	 * @var \HQ\Model\Entity\LstErrorStatus
	 */
	protected $lstErrorStatus;

	/**
	 * @autowire
	 * @var \Nette\Caching\Cache
	 */
	protected $cache;

	/**
	 * @autowire
	 * @var \HQ\ErrorMonitorinq\ImportService
	 */
	protected $importService;

	public function actionDefault() {
		$this->template->lastUpdate = $this->cache->load("lastUpdate", function () {
			return null;
		});
	}

	public function actionDisplay($id) {
		$errorRow = $this->errorEntity->find($id);
		$dataSourceClass = $errorRow->ref("project_id")->data_source;
		$dataSource = $this->getContext()->getByType($dataSourceClass);

		if ($dataSource instanceof \HQ\ErrorMonitorinq\Datasource\IDataSource) {
			$this->template->content = $dataSource->getFileContent($errorRow->remote_file);
		}
	}

	public function handleArchive($id) {
		$this->errorEntity->archive($id);
		$this->invalidateControl();
	}

	public function handleLoadExceptions() {
		$this->importService->import();
		$this->template->lastUpdate = $this->cache->load("lastUpdate", function () {
			return null;
		});
		$this->invalidateControl();
	}

	public function handleProjectScan() {
		$this->importService->importProjects();
		$this->invalidateControl();
	}

	public function createComponentErrorGrid() {
		$selection = $this->errorEntity->findAll();
		return new \FrontendModule\Components\Grids\ErrorGrid(
			$selection,
			$this->projectEntity,
			$this->lstErrorStatus,
			$this->errorEntity
		);
	}
}