<?php

namespace AdminModule;

use BackendModule;

use Nette\Security\User;

abstract class BasePresenter extends \BasePresenter
{

	public function startup()
	{
		parent::startup();

		if (!$this->user->isLoggedIn()) {
			$this->redirect(':Frontend:Homepage:');
		}
 	}

	public function actionLogout()
	{
		$this->user->logout();
		$this->redirect(':Frontend:Homepage:');
	}
}
