<?php

namespace HQ\ErrorMonitorinq;

class ImportService extends \Nette\Object {

	/** @var \HQ\ErrorMonitorinq\Datasource\IDataSource */
	protected $dataSource;

	/** @var \HQ\ErrorMonitoring\Nette\ExceptionParser */
	protected $exceptionParser;

	/** @var \HQ\Model\Entity\LstErrorStatus */
	protected $lstErrorStatus;

	/** @var \HQ\Model\Entity\ProjectEntity */
	protected $projectEntity;

	/** @var \HQ\Model\Entity\ErrorEntity */
	protected $errorEntity;

	/** @var \Nette\Caching\Cache */
	protected $cache;

	protected $tempDir;

	public function __construct(
		$tempDir,
		\HQ\ErrorMonitorinq\Datasource\IDataSource $dataSource,
		\HQ\ErrorMonitoring\Nette\ExceptionParser $exceptionParser,
		\HQ\Model\Entity\LstErrorStatus $lstErrorStatus,
		\HQ\Model\Entity\ProjectEntity $projectEntity,
		\HQ\Model\Entity\ErrorEntity $errorEntity,
		\Nette\Caching\Cache $cache
	) {
		$this->tempDir = $tempDir;
		$this->dataSource = $dataSource;
		$this->exceptionParser = $exceptionParser;
		$this->lstErrorStatus = $lstErrorStatus;
		$this->projectEntity = $projectEntity;
		$this->errorEntity = $errorEntity;
		$this->cache = $cache;
	}

	public function import() {

		$projects = $this->projectEntity->fetchPairs("name", null);

		$statusNewRow = $this->lstErrorStatus->findOneBy(array(
			"status" => "New"
		));

		foreach ($projects as $projectName => $index) {
			$fileList = $this->dataSource->getFileList("$projectName/exception");

			foreach ($fileList as $file) {
				if (pathinfo($file->name, PATHINFO_EXTENSION) != "html") {
					continue;
				}

				$errorRow = $this->errorEntity->findOneBy(array(
					"source_file" => $file->name
				));

				if (!$errorRow) {
					$errorFileContent = $this->dataSource->getFileContent($file->name);
					$archiveFilePath = $this->dataSource->moveToArchive($file->name);
					$this->exceptionParser->parse($errorFileContent);

					$this->errorEntity->insert(array(
						"project_id" => $projects[$projectName]->id,
						"error_status_id" => $statusNewRow->id,
						"title" => $this->exceptionParser->getTitle(),
						"message" => $this->exceptionParser->getMessage(),
						"source_file" => $this->exceptionParser->getSourceFile(),
						"remote_file" => $archiveFilePath,
						"error_dt" => $file->lastModified,
						"ins_process_id" => __METHOD__
					));
					break;
				}
			}
		}

		$this->cache->save("lastUpdate", new \DateTime);
	}

	public function importProjects() {
		$fileList = $this->dataSource->getFileList();
		$projects = $this->projectEntity->fetchPairs("name", null);

		foreach ($fileList as $file) {
			list($folder) = explode("/", $file->name);

			if (!array_key_exists($folder, $projects)) {
				$projects[$folder] = $this->projectEntity->insert(array(
					"name" => $folder,
					"data_source" => get_class($this->dataSource),
					"ins_process_id" => __METHOD__
				));
			}
		}
	}
}