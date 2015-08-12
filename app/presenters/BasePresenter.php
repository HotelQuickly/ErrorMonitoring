<?php

use Nette\Diagnostics\Debugger;
use WebLoader\JavaScriptLoader;
use WebLoader\VariablesFilter;
use WebLoader\CssLoader;
use \Nette\Caching\Cache;
use Nette\Mail\Message;
use \Nette\Localization\ITranslator;

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {

	use Kdyby\Autowired\AutowireProperties;
	use Kdyby\Autowired\AutowireComponentFactories;

	public $lang;

	/** @autowire @var \HQ\Logger */
	protected $logger;

	/** @autowire @var \HQ\Template\TemplateFactory */
	protected $templateFactory;

	/** @autowire @var \Nette\Localization\ITranslator */
	protected $translator;

	protected $lastLogItem;

	public function startup()
	{
		parent::startup();
		\Nette\Diagnostics\Debugger::timer('global');

		$httpRequest = $this->getHttpRequest();

		// Log visits
		$ipAddress = (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $this->getHttpRequest()->getRemoteAddress() : $_SERVER['HTTP_X_FORWARDED_FOR']);
		// ip address can be more values divided by comma (it's path to remote server), take only the last (most accurate IP of visitor)
		$ipAddressParts = explode(',', $ipAddress);
		$ipAddress = array_pop($ipAddressParts);
		if ($httpRequest->getUrl()->path != '/healthy-check') {

			$this->lastLogItem = $this->logger->logVisit(
				$httpRequest->getUrl()->path, // URL
				$ipAddress,  // IP
				$httpRequest->getHeader('User-Agent'),  // USER_AGENT
				$httpRequest->getReferer()  // REFERRER
			);
		}

	}


	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->gaCode = $this->context->parameters['gaCode'];
	}

	public function shutdown($response)
	{
		parent::shutdown($response);
		if ($this->getHttpRequest()->getUrl()->path != '/healthy-check') {
			$elapsed = \Nette\Diagnostics\Debugger::timer('global');
			// Log Valid Response
			$data = array(
				'elapsed' => $elapsed,
				'upd_process_id' => 'BasePresenter::shutdown()',
			);
			if ($this->lastLogItem) {
				$this->logger->updateLogVisit($this->lastLogItem->id, $data);
			}
		}
	}

	protected function createComponentJs()
	{
		$files = new WebLoader\FileCollection($this->context->parameters['wwwDir'] . '/js');
		$compiler = WebLoader\Compiler::createJsCompiler($files, $this->context->parameters['wwwDir'] . '/webtemp');
		return new WebLoader\Nette\JavaScriptLoader($compiler, '/webtemp');
	}


	protected function createComponentCss()
	{
		$files = new WebLoader\FileCollection($this->context->parameters['wwwDir'] . '/css');
		$compiler = WebLoader\Compiler::createCssCompiler($files, $this->context->parameters['wwwDir'] . '/webtemp');
		return $control = new WebLoader\Nette\CssLoader($compiler, '/webtemp');
	}

	/**
	 * Creates basic template (used for presenters)
	 * @param  string|NULL	$className
	 * @return Nette\Templating\IFileTemplate
	 */
	public function createTemplate($className = NULL)
	{
		return $this->templateFactory->createTemplate($this, null, $this->lang, $className);
	}

	/**
	 * Overwrite default $message by translating it
	 * @param  string
	 * @param  string
	 * @return \stdClass
	 */
	public function flashMessage($message, $type = "info")
	{
		$message = $this->translator->translate($message);
		return parent::flashMessage($message, $type);
	}

}
