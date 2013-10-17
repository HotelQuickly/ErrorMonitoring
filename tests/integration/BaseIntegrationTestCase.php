<?php

namespace Tests;
use Nette,
	Tester;

/**
 *  Defines basic envirenment for integration testing, mainly connection to database
 *
 * @author Josef Nevoral <josef.nevoral@gmail.com>
 */
class BaseIntegrationTestCase extends BaseTestCase
{

	/** @var \Nette\Database\Connection */
	protected $database;

	public function __construct(\Nette\DI\Container $container)
	{
		parent::__construct($container);
		$this->database = $container->getService('database');
	}

	protected function setUp()
	{
		parent::setUp();
		$this->lockDb();
		$this->initDatabase();
		$this->startTransaction();
	}

	protected function tearDown()
	{
		parent::tearDown();
		$this->rollback();
	}


	public function truncate($tableName)
	{
		$this->database->exec('SET FOREIGN_KEY_CHECKS = 0;');
		$this->database->exec('TRUNCATE TABLE `' . $tableName . '`');
		$this->database->exec('SET FOREIGN_KEY_CHECKS = 1;');
	}

	public function truncateTables()
	{
		$args = func_get_args();
		foreach ($args[0] as $table) {
			$this->truncate($table);
		}
	}

	public function truncateAndInit()
	{
		$this->truncateTables();
		$this->initDatabase();
	}

	public function startTransaction()
	{
		$this->database->exec('START TRANSACTION');
	}
	public function commit()
	{
		$this->database->exec('COMMIT');
	}
	public function rollback()
	{
		$this->database->exec('ROLLBACK');
	}

	public function lockDb()
	{
		Tester\Helpers::lock('db', dirname(TEMP_DIR));
	}


	private function initDatabase($fileName = null)
	{
		$reflection = \Nette\Reflection\ClassType::from(get_class($this));
		$dir = dirname($reflection->getFileName());

		$fileName = $fileName ? $fileName : $dir . '/init.sql';
		$initSql = file_get_contents($fileName);

		$queries = explode(';', $initSql);
		foreach ($queries as $query) {
			if (empty($query)) {
				continue;
			}
			$this->database->query(trim($query));
		}
	}


}