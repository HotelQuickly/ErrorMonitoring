<?php

namespace HQ\ErrorMonitorinq\Datasource;

interface IDataSource {
	/** @var \Iterator */
    public function getFileList($folder = "");
    public function getFileContent($filePath);
	public function moveToArchive($filePath);
}
