<?php

namespace HQ\ErrorMonitorinq;

class ImportService extends \Nette\Object {

	/** @var \HQ\ErrorMonitorinq\Datasource\IDataSource */
	protected $dataSource;

	/** @var \HQ\Model\Entity\LstProjectEntity */
	protected $lstProjectEntity;

	/** @var \HQ\Model\Entity\ErrorEntity */
	protected $errorEntity;

	public function __construct(
		\HQ\ErrorMonitorinq\Datasource\IDataSource $dataSource,
		\HQ\Model\Entity\LstProjectEntity $lstProjectEntity,
		\HQ\Model\Entity\ErrorEntity $errorEntity
	) {
		$this->dataSource = $dataSource;
		$this->lstProjectEntity = $lstProjectEntity;
		$this->errorEntity = $errorEntity;
	}

	public function import() {
		$fileList = $this->dataSource->getFileList();
		$projects = $this->lstProjectEntity->fetchPairs("name", null);

		foreach ($fileList as $file) {
			list($folder) = explode("/", $file->name);

			if (!array_key_exists($folder, $projects)) {
				$projects[$folder] = $this->lstProjectEntity->insert(array(
					"name" => $folder,
					"ins_process_id" => __METHOD__
				));
			}

			$errorRow = $this->errorEntity->findOneBy(array(
				"source" => $file->name
			));

			if (!$errorRow) {
				$this->errorEntity->insert(array(
					"project_id" => $projects[$folder]->id,
					"source" => $file->name,
					"name" => "TODO",
					"error_dt" => $file->lastModified,
					"ins_process_id" => __METHOD__
				));
			}
		}
	}
}