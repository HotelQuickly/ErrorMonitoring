<?php

namespace HQ;

use Nette,
	Nette\Caching\Cache;
use HQ\Model\Entity;

class DbTranslator extends Nette\Object implements Nette\Localization\ITranslator {

	/** @var \Models\TranslationMsg */
	protected $translationMsgModel;

	/** @var \Models\Translation */
	protected $translationModel;

	/** @var \Models\LstLang */
	protected $langEntity;

	/** @var \Models\TranslationMsgIgnore */
	protected $translationMsgIgnoreModel;

	/** @var \HQ\Logger */
	protected $logger;

	/** @var \Nette\Http\Request */
	protected $httpRequest;

	/** @var \Nette\Caching\Cache */
	private $cache;

	/** @var string */
	private $langTo;

	/** @var string */
	private $langToDefault = 'en';

	/** @var string */
	private $langFrom;

	/** @var string */
	private $langFromDefault = 'en';

	/**
	 * Translated strings loaded from cache
	 * @var array
	 */
	private $translationsArray = array();

	/**
	 * All strings loaded from cache
	 * @var array
	 */
	private $stringsArray = array();

    public function __construct(
		Cache $cache,
		Logger $logger,
		Nette\Http\Request $httpRequest,
		Entity\LstLangEntity $langEntity
	) {
    	$this->langEntity = $langEntity;
		$this->cache = $cache;
		$this->logger = $logger;
		$this->httpRequest = $httpRequest;
    }


	/**
	 * Returns cached array with all strings
	 * @return array
	 */
	public function getStringsArray()
	{
		// Check if strings are already loaded
		if(empty($this->stringsArray)){
			// Try to load the cache
			$messagesArray = $this->cache->load($this->getStringsCacheName());
			if(empty($messagesArray)){
				// Strings does not exist in cache, load them from db
				$this->loadStrings();
			} else {
				// Translations are cached, just load them
				$this->stringsArray = $messagesArray;
			}
		}
		return $this->stringsArray;
	}


	/**
	 * Loads cache or generate it from database if does not exists
	 * @return array
	 */
	public function getTranslationsArray(){
		$langFrom = $this->getLangFrom();
		$langTo = $this->getLangTo();

		// Check local object with translations
		if(empty( $this->translationsArray[$langFrom][$langTo] )){
			// Try to load the cache
			$translationsArray = $this->cache->load($this->getCacheName($langFrom, $langTo));
			if(empty($translationsArray)){
				// Translations are not cached yet load them from db
				$this->loadTranslations($langFrom, $langTo);
			}
			else {
				// Translations are cached, just load them
				$this->translationsArray[$langFrom][$langTo] = $translationsArray;
			}
		}
		// Return local object for better performance
		return $this->translationsArray[$langFrom][$langTo];
	}


	/**
	 * Setter for $this->langTo
	 * @param string $lang
	 * @return \HQ\DbTranslator
	 */
	public function setLangTo($lang = null) {
		if (empty($lang)) {
			$lang = $this->langToDefault;
		}
        $this->langTo = substr($lang, 0, 2);
		return $this;
	}


	/**
	 * Setter for $this->langFrom
	 * @param string $lang
	 * @return \HQ\DbTranslator
	 */
	public function setLangFrom($lang = null) {
		if (empty($lang)) {
			$lang = $this->langFromDefault;
		}
        $this->langFrom = substr($lang, 0, 2);
		return $this;
	}


	/**
	 * Getter for $this->langTo
	 * @return string
	 */
	public function getLangTo() {
        return ($this->langTo ? $this->langTo : $this->langToDefault);
	}


	/**
	 * Getter for $this->langFrom
	 * @return string
	 */
	public function getLangFrom() {
        return ($this->langFrom ? $this->langFrom : $this->langFromDefault);
	}


	/**
	 * Get cachename for given languages
	 * @param string $langFrom
	 * @param string $langTo
	 * @return string
	 */
	public function getCacheName($langFrom, $langTo) {
		return 'translations.'.$langFrom.'.'.$langTo;
	}


	/**
	 * Get cachename for strings array
	 * @return string
	 */
	public function getStringsCacheName() {
		return "translationStrings";
	}


	/**
	 * Clears cache file by given languages
	 * @param string $langFrom
	 * @param string $langTo
	 * @return HQ\DbTranslator
	 */
	public function clearCache($langFrom, $langTo) {
		$cacheName = $this->getCacheName($langFrom, $langTo);
		$this->cache->remove($cacheName);
		return $this;
	}


	/**
	 * Clears strings cache
	 * @return HQ\DbTranslator
	 */
	public function clearStringsCache() {
		$cacheName = $this->getStringsCacheName();
		$this->cache->remove($cacheName);
		return $this;
	}


    /**
     * Translates the given string.
     * @param  string   message
     * @param  int      plural count
     * @return string   translated string
     */
    public function translate($message, $param1 = null, $param2 = null, $param3 = null) {
		$langFrom = $this->getLangFrom();
		$langTo = $this->getLangTo();

		 // If languages are same or no languages given, return original message
		if( ($langFrom == $langTo) || !$langFrom || !$langTo || empty($message) || is_numeric($message) ){
			return sprintf($message, $param1, $param2, $param3);
		} else {
			// Fetch translated strings
			$translationsArray = $this->getTranslationsArray();
			$stringsArray = $this->getStringsArray();

			// Check for given message if is translated
			$messageTranslated = isset($translationsArray[$langFrom][md5($message)][$langTo]) ? $translationsArray[$langFrom][md5($message)][$langTo] : null;
			if ( !empty($messageTranslated) ) {
				$messageOut = sprintf($messageTranslated, $param1, $param2, $param3);
			} else {
				// The string should not be translated, please log where it is found!
				if(!$this->isUsedToTranslate($message)){
					$this->logger->log('String that should not be translated found', 'DbTranslator', $this->httpRequest->getUrl(), $message);
					return sprintf($message, $param1, $param2, $param3);
				}

				// Check for given message if is in untranslated cache
				$messageCached = isset($stringsArray[md5($message)]) ? $stringsArray[md5($message)] : null;
				if( !empty($messageCached) ){
					$messageOut = sprintf($messageCached, $param1, $param2);
				}
				else{
					// String is not translated yet
					$messageOut = sprintf($message, $param1, $param2, $param3);
					$this->saveNonTranslatedString($message);
				}
			}
			return $messageOut;
		}
    }


    /**
     * Pushes all data from given array into database
	 * @param string $stringsArray
     */
    public function saveNonTranslatedString($message) {
		// Prepare data for db
		$data = array(
			'message' => $message,
			'length' => strlen($message),
			'ins_dt' => new \DateTime(),
			'ins_process_id' => 'DbTranslator::addMissingMessages('.$_SERVER['REQUEST_URI'].')',
		);
		$row = $this->translationMsgModel->insertIgnore($data);
		$this->clearStringsCache();
		return $row;
	}


	/**
	* Check if the string is used to be translated
	* @param string $message
	* @return boolean
	*/
	public function isUsedToTranslate($message) {
		$row = $this->translationMsgIgnoreModel->findByMessage($message);
		return (true && !$row);
	}


	/**
	 * Loads all strings from database into cache
	 * @return HQ\DbTranslator
	 */
	private function loadStrings()
	{
		$messagesArray = array();

		$translationMessages = $this->translationMsgModel->findAll()
			->select("message");

		foreach($translationMessages as $translationMsg) {
			$messagesArray[md5($translationMsg->message)] = $translationMsg->message;
		}

		$this->cache->save($this->getStringsCacheName(), $messagesArray, array(Cache::EXPIRE => '1 day'));
		$this->stringsArray = $this->cache->load($this->getStringsCacheName());

		return $this;
	}


	/**
	 * Loads translations from database into cache
	 * @return HQ\DbTranslator
	 */
	private function loadTranslations()
	{
		$translationsArray = array();
		$langFrom = $this->getLangFrom();
		$langTo = $this->getLangTo();
		$translations = $this->translationModel->findTranslatedByLangCodes($langFrom, $langTo);

		foreach($translations as $translation) {
			$translationsArray[$langFrom][md5($translation->message)][$langTo] = $translation->message_translated;
		}

		$this->cache->save($this->getCacheName($langFrom, $langTo), $translationsArray, array(Cache::EXPIRE => '1 day'));
		$this->translationsArray[$langFrom][$langTo] = $this->cache->load($this->getCacheName($langFrom, $langTo));

		return $this;
	}

}
