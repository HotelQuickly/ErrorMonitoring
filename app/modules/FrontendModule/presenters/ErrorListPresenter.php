<?php

namespace FrontendModule;

class ErrorListPresenter extends BasePresenter {

	/**
	 * @autowire
	 * @var \HQ\Model\Entity\ErrorEntity
	 */
	protected $errorEntity;

	/**
	 * @autowire
	 * @var \HQ\Model\Entity\LstProjectEntity
	 */
	protected $lstProjectEntity;

	public function actionDefault() {

	}

	public function actionDisplay($id) {
		$errorRow = $this->errorEntity->find($id);
		$dataSourceClass = $errorRow->ref("project_id")->data_source;
		$dataSource = $this->getContext()->getByType($dataSourceClass);

		if ($dataSource instanceof \HQ\ErrorMonitorinq\Datasource\IDataSource) {
			$this->template->content = $dataSource->getFileContent($errorRow->remote_file);
		}
	}

	public function handleSolve($id) {
		$this->errorEntity->solve($id);
		$this->invalidateControl();
	}

	public function createComponentErrorGrid() {
		$selection = $this->errorEntity
			->findAll()
			->where("solved_flag", 0);

		return new \FrontendModule\Components\Grids\ErrorGrid($selection, $this->lstProjectEntity);
	}
}