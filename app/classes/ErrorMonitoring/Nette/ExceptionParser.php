<?php

namespace HQ\ErrorMonitoring\Nette;

class ExceptionParser extends \Nette\Object {

	protected $title;
	protected $message;
	protected $sourceFile;

	/** @var \DOMDocument */
	protected $domDocument;

	public function parse($html)
	{

		if (!$this->domDocument) {
			$this->domDocument = new \DOMDocument();
		}

		@$this->domDocument->loadHTML($html);

		$titleItem = $this->domDocument
			->getElementsByTagName("title")
			->item(0);

		$this->title = $titleItem ? $titleItem->textContent : 'N/A';

		$sourceFileElement = $this->domDocument->getElementById("netteBsPnl1");
		$sourceFileLinkNode = $sourceFileElement->getElementsByTagName("a")->item(0);

		$this->sourceFile = trim($sourceFileLinkNode->textContent);
		try {
			$messageNode = $this->domDocument
				->getElementsByTagName("p")
				->item(0);

			$messageNode->removeChild($messageNode->lastChild);
			$this->message = trim($messageNode->textContent);
		} catch (\Exception $e) {
			$this->message = 'Unable to parse';
		};
	}

	public function getTitle() {
		return $this->title;
	}

	public function getMessage() {
		return $this->message;
	}

	public function getSourceFile() {
		return $this->sourceFile;
	}
}