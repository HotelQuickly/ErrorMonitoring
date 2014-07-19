<?php


namespace HQ\Factory\Form;
use Nette\Application\UI\Form;
use Nette\Security\User;

/**
 * Class LoginFormFactory
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */
class LoginFormFactory extends \Nette\Object
{
	/** @var  BaseFormFactory */
	private $baseFormFactory;

	/** @var  User */
	private $userSecurity;

	public function __construct(
		BaseFormFactory $baseFormFactory,
		User $userSecurity
	) {
		$this->baseFormFactory = $baseFormFactory;
		$this->userSecurity = $userSecurity;
	}

	public function create()
	{
		$form = $this->baseFormFactory->create();
		$form->getElementPrototype()->setClass('');

		$form->addText('email', 'E-mail')
			->addRule(Form::FILLED, 'Please fill in your e-mail.')
			->addRule(Form::EMAIL, 'Please fill in a valid e-mail.')
			->setRequired('Please fill in your e-mail.');
		$form->addPassword('pass', 'Password:')
			->addRule(Form::FILLED, 'Please insert password.')
			->setRequired('Please insert your password.');
		$form->addSubmit('submit', 'Log in')
			->getControlPrototype()->class('btn btn-primary');

		$form->onSuccess[] = $this->process;

		return $form;
	}


	public function process(Form $form)
	{
		$values = $form->getValues();

		try {
			$this->userSecurity->setExpiration('+ 14 days', FALSE);
			$this->userSecurity->login($values->email, $values->pass);
		} catch (\Exception $e) {
			throw $e;
			$form->addError("We are sorry, error occured during login process");
			return;
		}

		// restore backlink if exists
		$form->presenter->restoreRequest($form->presenter->backlink);

		// or redirect
		$form->presenter->redirect(':Admin:ErrorList:');

	}
} 