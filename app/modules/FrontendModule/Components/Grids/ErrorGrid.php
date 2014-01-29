<?php

namespace FrontendModule\Components\Grids;

use \NiftyGrid\Grid;
use \Nette\Utils\Strings;

class ErrorGrid extends Grid {

	/** @var \Nette\Database\Table\Selection */
	protected $selection;

	/** @var \HQ\Model\Entity\ProjectEntity */
	protected $projectEntity;

	public function __construct(
		\Nette\Database\Table\Selection $selection,
		\HQ\Model\Entity\ProjectEntity $projectEntity
	) {
		parent::__construct();
		$this->selection = $selection;
		$this->projectEntity = $projectEntity;
	}

	protected function configure($presenter) {

		$this->selection->select("error.id, title, message, error_dt, project_id.name AS project_name");

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

		$this->addColumn("error_dt", "Date", "150px")
			->setDateFilter()
			->setSortable()
			->setRenderer(function($row) {
				return $row["error_dt"]->format("j.n.Y H:i:s");
			});

		$this->addButton("solve", "Solve")
			->setText("Solve")
			->setAjax()
			->setLink(function($row) use ($presenter){return $presenter->link("solve!", $row['id']);})
			->setClass("btn-info btn-solve");
	}

}
