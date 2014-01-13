<?php

namespace HQ;

use Nette;

/**
 *  @author Jan Mikes <jan.mikes@hotelquickly.com>
 *  @copyright HotelQuickly Ltd.
 */
class ConnectionPool extends Nette\Object {

	/** @var array */
	private $configs = array();

	/** @var array */
	private $connections = array();

	/** @var Nette\Caching\IStorage */
	private $cacheStorage;

	/** @var Nette\Database\IReflection */
	private $databaseReflection;

	/** @var bool */
	private $isProduction;


	public function __construct($isProduction, array $configs, Nette\Database\IReflection $databaseReflection, Nette\Caching\IStorage $cacheStorage)
	{
		$this->configs = $configs;
		$this->cacheStorage = $cacheStorage;
		$this->databaseReflection = $databaseReflection;
		$this->isProduction = $isProduction;
	}


	/**
	 * @param  string $name
	 * @return Nette\Database\Connection
	 */
	public function getConnection($name = "default")
	{
		if (!isset($this->connections[$name])) {
			$this->connections[$name] = $this->createConnection($name);
		}

		return $this->connections[$name];
	}


	/////////////////////
	// PRIVATE METHODS //
	/////////////////////


	/**
	 * @return Nette\Database\Connection
	 * @throws Nette\InvalidArgumentException
	 */
	private function createConnection($name)
	{
		if (empty($this->configs[$name])) {
			throw new Nette\InvalidArgumentException("Connection '$name' definition is missing in config!");
		}

		$config = $this->configs[$name];
		$connection = new Nette\Database\Connection($config["dsn"], $config["user"], $config["password"]);
		$connection->setCacheStorage($this->cacheStorage);
		$connection->setDatabaseReflection($this->databaseReflection);

		// Panels are not rendered on production but they are still logging!
		// Preventing them from log in production will decrease memory usage!
		if (!$this->isProduction) {
			$connectionPanel = new Nette\Database\Diagnostics\ConnectionPanel;
			Nette\Diagnostics\Debugger::$bar->addPanel($connectionPanel);
			$connection->onQuery[] = $connectionPanel->logQuery;
		}

		return $connection;
	}

}
