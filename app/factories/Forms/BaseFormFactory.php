<?php

namespace HQ\Factory\Form;

use Nette,
	Kdyby,
	Nette\Application\UI\Form;

class BaseFormFactory extends Nette\Object {

	/** @var Nette\Localization\ITranslator */
	private $translator;

	public function __construct(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;
	}


	public function create()
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form = $this->activateBootstrapRenderer($form);
		return $form;
	}


	public function activateBootstrapRenderer(Nette\Application\UI\Form $form)
	{
		$form->setRenderer(new Kdyby\BootstrapFormRenderer\BootstrapRenderer);
		return $form;
	}

}
