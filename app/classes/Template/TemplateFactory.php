<?php

namespace HQ\Template;

use Nette,
	Nette\Application\UI;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Bridges\CacheLatte\CacheMacro;
use Nette\Bridges\FormsLatte\FormMacros;

/**
 * Service for creating templates for all objects implementing Nette\Template\IFileTemplate
 *
 * @author	Jan Mikes <jan.mikes@hotelquickly.com>
 * @package	HQ.com
 */
final class TemplateFactory extends Nette\Object
{

	/** @var  Nette\DI\Container */
	private $container;

	/** @var  ILatteFactory */
	private $latteFactory;

	/** @var Nette\Localization\ITranslator */
	private $translator;

	/** @var string */
	private $appDir;

	/** @var Nette\Http\Response */
	private $httpResponse;

	/** @var Nette\Http\Request */
	private $httpRequest;

	/** @var Nette\Caching\IStorage */
	private $netteCacheStorage;


	public function __construct(
		$appDir,
		Nette\DI\Container $container,
		Nette\Localization\ITranslator $translator,
		Nette\Http\Response $httpResponse,
		Nette\Http\Request $httpRequest,
		Nette\Caching\IStorage $netteCacheStorage
	) {
		$this->container = $container;
		$this->latteFactory = $container->createServiceNette__latteFactory();
		$this->translator = $translator;
		$this->appDir = $appDir;
		$this->httpResponse = $httpResponse;
		$this->httpRequest = $httpRequest;
		$this->netteCacheStorage = $netteCacheStorage;
	}



	/**
	 * Creates template and registers helpers and latte filter
	 * @param  UI\Presenter $presenter
	 * @param  string		$file	Filepath to file
	 * @param  string|NULL	$lang	Lang code (length=2)
	 * @param  string		$class	Name of template class
	 * @throws InvalidArgumentException
	 * @return Nette\Templating\IFileTemplate
	 */
	public function createTemplate(UI\Presenter $presenter, $file = NULL, $lang = NULL, $class = NULL)
	{
		$this->translator->setLangTo($lang);

		$latte = $this->latteFactory->create();
		$template = $class ? new $class($latte) : new Nette\Bridges\ApplicationLatte\Template($latte);

		$template->getLatte()->addFilter(NULL, 'Nette\\Templating\\Helpers::loader');
		$template->getLatte()->addFilter(NULL, '\\Helpers::loader');

		array_unshift($latte->onCompile, function($latte) {
			$latte->getParser()->shortNoEscape = TRUE;
			$latte->getCompiler()->addMacro('cache', new CacheMacro($latte->getCompiler()));
			Nette\Bridges\ApplicationLatte\UIMacros::install($latte->getCompiler());
			FormMacros::install($latte->getCompiler());
		});

		$latte->addFilter('url', 'rawurlencode'); // back compatiblity
		foreach (array('normalize', 'toAscii', 'webalize', 'padLeft', 'padRight', 'reverse') as $name) {
			$latte->addFilter($name, 'Nette\Utils\Strings::' . $name);
		}

		$template->setTranslator($this->translator);

		if (!is_null($file)) {
			$template->setFile($this->appDir . $file);
		}

		// default parameters
		$template->control = $template->_control = $presenter;
		$template->presenter = $template->_presenter = $presenter;
		if ($presenter instanceof UI\Presenter) {
			$template->user = $presenter->getUser();
			$template->netteHttpResponse = $this->httpResponse;
			$template->netteCacheStorage = $this->netteCacheStorage;
			$template->baseUri = $template->baseUrl = rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/');
			$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);

			// flash message
			if ($presenter->hasFlashSession()) {
				$id = $presenter->getParameterId('flash');
				$template->flashes = $presenter->getFlashSession()->$id;
			}
		}
		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes = array();
		}

		return $template;
	}

}
