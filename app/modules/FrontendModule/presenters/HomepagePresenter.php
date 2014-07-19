<?php

namespace FrontendModule;

class HomepagePresenter extends BasePresenter
{
	/** @autowire
	 * @var  \HQ\Factory\Form\LoginFormFactory */
	protected $loginFormFactory;


	public function actionDefault()
	{

		if ($this->user->isLoggedIn()) {
			$this->redirect(':Admin:ErrorList:');
		}
	}

	public function createComponentLoginForm()
	{
		return $this->loginFormFactory->create();
	}
}
