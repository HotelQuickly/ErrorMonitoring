<?php

namespace FrontendModule\Components\Grids;

use \NiftyGrid\Grid;
use \Nette\Utils\Strings;

class ErrorGrid extends Grid {

	/** @var \Nette\Database\Table\Selection */
	protected $selection;

	/** @var \HQ\Model\Entity\ProjectEntity */
	protected $projectEntity;

	/** @var \HQ\Model\Entity\LstErrorStatus */
	protected $lstErrorStatus;

	public function __construct(
		\Nette\Database\Table\Selection $selection,
		\HQ\Model\Entity\ProjectEntity $projectEntity,
		\HQ\Model\Entity\LstErrorStatus $lstErrorStatus
	) {
		parent::__construct();
		$this->selection = $selection;
		$this->projectEntity = $projectEntity;
		$this->lstErrorStatus = $lstErrorStatus;
	}

	protected function configure($presenter) {

		$this->selection->select("error.id, title, message, error_dt, project_id.name AS project_name, error_status_id.status AS status");

		$source = new \NiftyGrid\DataSource\NDataSource($this->selection);

		$this->setDataSource($source);
		$this->setDefaultOrder("error_dt DESC");

		$this->addColumn("project_name", "Project")
			->setTableName("project_id")
			->setSortable()
			->setSelectFilter(
				$this->projectEntity->findAll()->fetchPairs("id", "name")
			);

		$this->addColumn("title", "Title")
			->setTextFilter()
			->setSortable();

		$this->addColumn("message", "Message")
			->setSortable()
			->setTextFilter()
			->setRenderer(function($row) use ($presenter) {
				return \Nette\Utils\Html::el("a")
					->setText(Strings::truncate($row["message"], 60))
					->addAttributes(array("target" => "_blank"))
					->href($presenter->link("ErrorList:display", $row["id"]));
			});

		$this->addColumn("status", "Status")
			->setTableName("error_status_id")
			->setSelectFilter(
				$this->lstErrorStatus->findAll()->fetchPairs("id", "status")
			)
			->setRenderer(function($row) use ($presenter) {
				$label = "";

				if ($row["status"] == "New") {
					$label = "label-important";
				}

				return \Nette\Utils\Html::el("span")
					->setText($row["status"])
					->addAttributes(array(
						"class" => "label $label"
					));
			});

		$this->addColumn("error_dt", "Date", "150px")
			->setDateFilter()
			->setSortable()
			->setRenderer(function($row) {
				return $row["error_dt"]->format("j.n.Y H:i:s");
			});

		$this->addButton("archive", "Archive")
			->setText("Archive")
			->setAjax()
			->setLink(function($row) use ($presenter){return $presenter->link("archive!", $row['id']);})
			->setClass("btn-info btn-solve");
	}

}
