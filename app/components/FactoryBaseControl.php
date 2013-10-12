<?php

namespace Components;

use Nette;

/**
 *  @author Jan Mikes <jan.mikes@hotelquickly.com>
 *  @copyright HotelQuickly Ltd.
 */
abstract class FactoryBaseControl extends Nette\Application\UI\Control {

	// Allows using factory injections without need of passing them through __construct
	// ie: protected function createSomeComponent(SomeFactory $someFactory) { return $someFactory->create(); }
	use \Kdyby\Autowired\AutowireComponentFactories;

	/** @var HotelQuickly\Template\TemplateFactory */
	protected $templateFactory;

	/** @var Nette\Caching\Cache */
	protected $cache;


	public function setTemplateFactory(HQ\Template\TemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}


	public function setCache(Nette\Caching\Cache $cache)
	{
		$this->cache = $cache;
	}


	public function render()
	{
		return $this->setupTemplate();
	}


	protected function createTemplate($class = NULL) {
		if (!is_null($this->templateFactory)) {
			return $this->templateFactory->createTemplate($this->getPresenter());
		}
		return parent::createTemplate($class);
	}


	protected function setupTemplate($view = "default", $filename = null)
	{
		$controlReflection = new Nette\Reflection\ClassType(get_class($this));
		$controlDir = dirname($controlReflection->getFileName());

		$filename = ($filename? $controlDir . DIRECTORY_SEPARATOR . $filename : $controlDir . DIRECTORY_SEPARATOR . "$view.latte");
		$this->template->setFile($filename);

		return $this->template;
	}

}
