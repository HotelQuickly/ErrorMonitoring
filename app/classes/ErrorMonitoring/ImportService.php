<?php

namespace HQ\ErrorMonitorinq;

class ImportService extends \Nette\Object {

	/** @var \HQ\ErrorMonitorinq\Datasource\IDataSource */
	protected $dataSource;

	/** @var \HQ\ErrorMonitoring\Nette\ExceptionParser */
	protected $exceptionParser;

	/** @var \HQ\Model\Entity\LstProjectEntity */
	protected $lstProjectEntity;

	/** @var \HQ\Model\Entity\ErrorEntity */
	protected $errorEntity;

	protected $tempDir;

	public function __construct(
		$tempDir,
		\HQ\ErrorMonitorinq\Datasource\IDataSource $dataSource,
		\HQ\ErrorMonitoring\Nette\ExceptionParser $exceptionParser,
		\HQ\Model\Entity\LstProjectEntity $lstProjectEntity,
		\HQ\Model\Entity\ErrorEntity $errorEntity
	) {
		$this->tempDir = $tempDir;
		$this->dataSource = $dataSource;
		$this->exceptionParser = $exceptionParser;
		$this->lstProjectEntity = $lstProjectEntity;
		$this->errorEntity = $errorEntity;
	}

	public function import() {
		$fileList = $this->dataSource->getFileList();
		$projects = $this->lstProjectEntity->fetchPairs("name", null);

		foreach ($fileList as $file) {

			if (pathinfo($file->name, PATHINFO_EXTENSION) != "html") {
				continue;
			}

			list($folder) = explode("/", $file->name);

			if (!array_key_exists($folder, $projects)) {
				$projects[$folder] = $this->lstProjectEntity->insert(array(
					"name" => $folder,
					"data_source" => get_class($this->dataSource),
					"ins_process_id" => __METHOD__
				));
			}

			$errorRow = $this->errorEntity->findOneBy(array(
				"source_file" => $file->name
			));

			$errorFileContent = $this->dataSource->getFileContent($file->name);
			$this->exceptionParser->parse($errorFileContent);

			if (!$errorRow) {
				$this->errorEntity->insert(array(
					"project_id" => $projects[$folder]->id,
					"title" => $this->exceptionParser->getTitle(),
					"message" => $this->exceptionParser->getMessage(),
					"source_file" => $this->exceptionParser->getSourceFile(),
					"remote_file" => $file->name,
					"error_dt" => $file->lastModified,
					"ins_process_id" => __METHOD__
				));
			}
		}
	}
}