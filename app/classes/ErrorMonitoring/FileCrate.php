<?php

namespace HQ\ErrorMonitorinq;

class FileCrate extends \Nette\Object {
	public $name;
	public $size;
	/** @var DateTime */
	public $lastModified;
}

