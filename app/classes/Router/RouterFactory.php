<?php

namespace HQ;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/** @var Boolean */
	private $secureRoutes;

	public function __construct($secureRoutes = false)
	{
		$this->secureRoutes = $secureRoutes;
	}

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();

		$secureRoutes = ($this->secureRoutes)? Route::SECURED : 0;

		// Changelog Router
		$changelogRouter = new RouteList('Changelog');
		$changelogRouter[] = new Route('changelog/<presenter>/<action>[/<id>]', 'Changelog:default', $secureRoutes);
		$router[] = $changelogRouter;

		// Cron Router
		$cronRouter = new RouteList('Cron');
		$cronRouter[] = new Route('cron/<presenter>/<action>[/<id>]', 'Homepage:default', $secureRoutes);
		$router[] = $cronRouter;

		// Task Router
		$taskRouter = new RouteList('Task');
		$taskRouter[] = new Route('task/<presenter>/<action>[/<id>]', 'Homepage:default', $secureRoutes);
		$router[] = $taskRouter;

		// One way routes
		$router[] = new Route('logout/', 'Secured:logout', Route::ONE_WAY);
		$router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
		$router[] = new Route('[<lang=en [a-z]{2}>/]homepage/new[/]', 'Homepage:default', Route::ONE_WAY);
		$router[] = new Route('[<lang=en [a-z]{2}>/]frontend.homepage/new[/]', 'Homepage:default', Route::ONE_WAY);

		// API Router
		$apiRouter = new RouteList('Api');
		$apiRouter[] = new Route('api/<apiVersion>/<presenter>[/<action>]', 'Homepage:default', $secureRoutes);
		$router[] = $apiRouter;

		$adminRouter = new RouteList('Admin');
		$adminRouter[] = new Route('admin/<presenter>/<action>[/<id>]', 'Homepage:default', $secureRoutes);
		$router[] = $adminRouter;

		// Frontend Router
		$frontendRouter = new RouteList('Frontend');
		$frontendRouter[] = new Route('healthy-check', 'HealthyCheck:default', $secureRoutes);
		$frontendRouter[] = new Route('[<lang=en [a-z]{2}>/]', 'Homepage:default', $secureRoutes);
		$frontendRouter[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default', $secureRoutes);
		$router[] = $frontendRouter;


		// Other routes
		$router[] = new Route('login/', 'Login:default', $secureRoutes);
		$router[] = new Route('error', 'Error:default', $secureRoutes);

		return $router;
	}

}
