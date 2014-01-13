<?php

namespace Tests;
use Nette,
    Tester;

/**
 *  Defines basic envirenment for testing
 *  Uses Mockista library as mocking tool
 *
 * @see https://bitbucket.org/jiriknesl/mockista/overview
 * @see https://github.com/janmarek/mockista
 * @author Josef Nevoral <josef.nevoral@gmail.com>
 */
abstract class BaseTestCase extends Tester\TestCase
{

    /** @var \Mockista\Registry */
    protected $mockista;

    /** @var \Nette\DI\Container */
	protected $container;

	public function __construct(\Nette\DI\Container $container = null)
	{
		$this->container = $container;
	}

    protected function setUp()
    {
        $this->mockista = new \Mockista\Registry();
    }

    protected function tearDown()
    {
        $this->mockista->assertExpectations();
    }

    public function getTableSelectionMock()
    {
        return $this->mockista->create(
            'Nette\Database\Table\Selection',
            array(
                'accessColumn' => true,
                'getDataRefreshed' => false,
                'getPrimary' => 'id'
            )
        );
    }
}