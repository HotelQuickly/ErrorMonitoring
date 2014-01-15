<?php

namespace HQ\ErrorMonitorinq\Datasource;

class S3DataSource extends \Nette\Object implements IDataSource {

    /** @var \HQ\Aws\S3Proxy */
    private $proxy;

    public function __construct(\HQ\Aws\S3Proxy $proxy) {
		$this->proxy = $proxy;
    }

	public function getFile($filePath, $targetPath) {
		throw new \Nette\NotImplementedException;
	}

	public function getFileList() {
		$filesIterator = $this->proxy->getFilesIterator();
		return new S3FilesIterator($filesIterator);
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