<?php

namespace Components;
use \Nette\Application\UI\Control,
	Nette,
	Nette\Caching\Cache;



abstract class BaseControl extends Control {

	/* @var NetteTranslator\Gettext */
	protected $translator;

	protected $cache;

	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->cache = new Cache(\Nette\Environment::getContext()->cacheStorage);
		//$this->monitor("\Nette\Application\UI\Presenter");
	}

	protected function createTemplate($class = NULL) {
		$template = parent::createTemplate($class);
		$template->registerHelperLoader('Helpers::loader');
		$this->translator = \Nette\Environment::getContext()->translator;
		$template->setTranslator($this->translator);
		$template->registerHelper('convert', callback(\Nette\Environment::getContext()->currencyHelper, 'convert'));
		$template->currentLang = $this->translator->getLangTo();
		return $template;
	}

}

