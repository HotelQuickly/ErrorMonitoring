<?php

namespace HQ\Factory;

use Nette;

class CacheFactory extends Nette\Object {

	/** @var Nette\Caching\Storages\FileStorage */
	private $fileStorage;

	/** @var Nette\Caching\Storages\MemcachedStorage */
	private $memcachedStorage;

	/** @var Nette\Caching\IStorage */
	private $cacheStorage;


	public function __construct(Nette\Caching\IStorage $cacheStorage, Nette\Caching\Storages\FileStorage $fileStorage, Nette\Caching\Storages\MemcachedStorage $memcachedStorage)
	{
		$this->cacheStorage = $cacheStorage;
		$this->fileStorage = $fileStorage;
		$this->memcachedStorage = $memcachedStorage;
	}


	public function create()
	{
		return new Nette\Caching\Cache($this->cacheStorage);
	}


	public function createFileStorageCache()
	{
		return new Nette\Caching\Cache($this->fileStorage);
	}


	public function createMemcachedStorageCache()
	{
		return new Nette\Caching\Cache($this->memcachedStorage);
	}

}
