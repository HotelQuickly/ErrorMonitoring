<?php

namespace Tests;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

/**
 *  Test: HotelQuickly\ConnectionPool
 * 
 *  @author Jan Mikes <jan.mikes@hotelquickly.com>
 *  @copyright HotelQuickly Ltd.
 */
class ConnectionPoolTestCase extends BaseTestCase {
	
	/** @var HotelQuickly\PoolService  */
	private $connectionPool;

	public function setUp()
	{
		parent::setUp();

		$isProduction = true;
		$parameters = $this->container->getParameters();
		$configs = $parameters["database"];
		$cacheStorage = $this->container->getByType("\Nette\Caching\IStorage");
		$databaseReflection = new \Nette\Database\Reflection\DiscoveredReflection($cacheStorage);

		$this->connectionPool = new \HotelQuickly\ConnectionPool($isProduction, $configs, $databaseReflection, $cacheStorage);
	}

	public function testGetConnection()
	{
		Assert::exception(function() {
			$this->connectionPool->getConnection("someRandomUnexistingDatabaseConnectionName");
		}, "\Nette\InvalidArgumentException");

		Assert::type("\Nette\Database\Connection", $this->connectionPool->getConnection("default"));
	}

}

\run(new ConnectionPoolTestCase($container));
