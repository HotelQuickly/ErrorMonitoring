<?php

namespace HQ\ErrorMonitorinq\Datasource;

interface IDataSource {
	/** @var \Iterator */
    public function getFileList();
    public function getFile($filePath, $targetPath);
}
