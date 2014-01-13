<?php

namespace HQ\Template;

use Nette,
	Nette\Application\IPresenter,
	Nette\Application\UI,
	Nette\Templating\IFileTemplate;

/**
 * Service for creating templates for all objects implementing Nette\Template\IFileTemplate
 *
 * @author	Jan Mikes <jan.mikes@hotelquickly.com>
 * @package	HQ.com
 */
final class TemplateFactory extends Nette\Object {

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
		Nette\Localization\ITranslator $translator,
		Nette\Http\Response $httpResponse,
		Nette\Http\Request $httpRequest,
		Nette\Caching\IStorage $netteCacheStorage
	) {
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

		$template = $class ? new $class : new FileTemplate;

		if (!$template instanceof IFileTemplate) {
			throw new \InvalidArgumentException('$template must be instance of Nette\\Templating\\IFileTemplate instead given ' . $class .' given!');
		}

		$template->setTranslator($this->translator);

		$template->registerHelperLoader('Nette\Templating\Helpers::loader');
		$template->registerHelperLoader('\Helpers::loader');

		$latte = new Nette\Latte\Engine;
		\Kdyby\BootstrapFormRenderer\Latte\FormMacros::install($latte->compiler);
		$template->registerFilter($latte);


		if (!is_null($file)) {
			$template->setFile($this->appDir . $file);
		}

		// default parameters
		$template->control = $template->_control = $presenter;
		$template->presenter = $template->_presenter = $presenter;
		if ($presenter instanceof UI\Presenter) {
			$template->setCacheStorage($presenter->getContext()->getService('nette.templateCacheStorage'));
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
