<?php

namespace HQ\ErrorMonitorinq\Datasource;

class S3DataSource extends \Nette\Object implements IDataSource {

    /** @var \HQ\Aws\S3Proxy */
    private $proxy;

	private $tempDir;

    public function __construct($tempDir, \HQ\Aws\S3Proxy $proxy) {
		$this->tempDir = $tempDir;
		$this->proxy = $proxy;
    }

	public function getFileContent($filePath)
	{
		$fileName = uniqid() . ".tmp";
		$targetPath = $this->tempDir . "/" . $fileName;

		try {
			$this->proxy->downloadFile($filePath, $targetPath);
			$content = file_get_contents($targetPath);
		} catch (\Aws\S3\Exception\NoSuchKeyException $e) {
			$content = '';
		};
		unlink($targetPath);
		return $content;
	}

	public function getFileList($folder = "")
	{
		$filesIterator = $this->proxy->getFilesIterator($folder);
		return new S3FilesIterator($filesIterator);
	}

	public function moveToArchive($filePath) {
		$targetFilePath = substr_replace($filePath, "/archive", strpos($filePath, "/"), 0);
		$this->proxy->moveFile($filePath, $targetFilePath);
		return $targetFilePath;
	}

}

class S3FilesIterator implements \Iterator {

	/** @var \Iterator */
	private $iterator;

	public function __construct($iterator) {
		$this->iterator = $iterator;
	}

	public function current() {
		$current = $this->iterator->current();

		$fileCrate = new \HQ\ErrorMonitorinq\FileCrate;
		$fileCrate->name = $current["Key"];
		$fileCrate->size = $current["Size"];
		$fileCrate->lastModified = new \DateTime($current["LastModified"]);

		return $fileCrate;
	}

	public function key() {
		return $this->iterator->key();
	}

	public function next() {
		return $this->iterator->next();
	}

	public function rewind() {
		return $this->iterator->rewind();
	}

	public function valid() {
		return $this->iterator->valid();
	}

}