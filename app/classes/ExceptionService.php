<?php

namespace HQ;

use Nette;
use HQ\Model\Entity;

class ExceptionService extends Nette\Object {

	private $logError;
	private $logDir;
	private $lstErrorTp;
	private $logger;

	public function __construct(
		$appDir,
		 \HQ\Logger $logger,
		 Entity\LogErrorEntity $logError,
		 Entity\LstErrorTpEntity $lstErrorTp
	) {
		$this->logError = $logError;
		$this->lstErrorTp = $lstErrorTp;
		$this->logger = $logger;
		$this->logDir = realpath($appDir . '/../log');
	}

	/**
	 * Returns path to the given exception $filename
	 * @param string $filename
	 * @return string
	 */
	public function getExceptionFilePath($filename){
		return realpath($this->logDir . '/' . $filename);
	}


	/**
	 * Parse all the exception files found in /log/*.html
	 * Checks if they are in log_error table, if not it includes them there
	 */
	public function parseFiles(){

		foreach (Nette\Utils\Finder::findFiles('*.html')->in($this->logDir) as $file) {

			$lstErrorTp = $this->lstErrorTp->getByName("exception");

			$filePath = $this->getExceptionFilePath($file->getFilename());

			if(is_file($filePath)){

				$parse = file_get_contents($filePath, false, null, -1, 600);
				$titleStart = strpos($parse, "<title>");
				$titleLineEnds = strpos($parse, "-->", $titleStart);

				$message = str_replace(array(
						"<title>",
						"</title><!-- "
					),
					array(
						"",
						": ",
					),
					substr($parse, $titleStart, $titleLineEnds-$titleStart));

				if(empty($message) || strlen($message) < 3){
					$message = "Unparsable name!";
				}

				$row = $this->logError->getTable()
					->where(array(
						"log_error.del_flag" => 0,
						"message" => $message,
						"error_tp.name" => "exception",
						"url" => $file->getFilename(),
						"log_error.ins_dt" => date('Y-m-d H:i:s', $file->getMTime()),
					))
					->fetch();

				if(!$row) {
					$inserted = $this->logError->getTable()
						->insert(array(
							"message" => $message,
							"file_content" => file_get_contents($filePath),
							"url" => $file->getFilename(),
							"ins_dt" => date('Y-m-d H:i:s', $file->getMTime()),
							"error_tp_id" => $lstErrorTp->id,
							"del_flag" => 0,
							"ins_process_id" => "HQ\\ExceptionService::parseFiles()"
						));
					if($inserted){
						unlink($filePath);
					}
				}
			}
			else {
				$this->logger->logError("404", "Could not reach exception file to parse: " . $file->getFileName());
			}
		}

	}

}