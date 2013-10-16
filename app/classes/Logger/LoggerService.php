<?php

namespace HQ;

use Nette,
	Nette\Diagnostics as ND,
	HQ\Model\Entity;

/**
 * Class for loging to database
 *
 * @author Josef Nevoral <josef.nevoral@gmail.com>
 */
class Logger {

	private $ajax;
	private $httpRequest;
	private $user;
	private $serverAddress;

	/** @var Nette\Database\Table\ActiveRow|FALSE|NULL */
	private $lastLogVisitRow = null;

	/** @var \HQ\Model\Entity\LogVisitEntity */
	private $logVisitEntity;

	/** @var \HQ\Model\Entity\LogEntity */
	private $logEntity;

	/** @var \HQ\Model\Entity\LogErrorEntity */
	private $logErrorEntity;

	/** @var \HQ\Model\Entity\LstErrorTpEntity */
	private $lstErrorTpEntity;

	const INFO = 'info';
	const ERORR = 'error';
	const CRITICAL = 'critical';

	public function __construct(
		Nette\Http\Request $httpRequest,
		Nette\Security\User $user,
		Entity\LogEntity $logEntity,
		Entity\LogErrorEntity $logErrorEntity,
		Entity\LogVisitEntity $logVisitEntity,
		Entity\LstErrorTpEntity $lstErrorTpEntity
	) {
		$this->logVisitEntity = $logVisitEntity;
		$this->logEntity = $logEntity;
		$this->logErrorEntity = $logErrorEntity;
		$this->lstErrorTpEntity = $lstErrorTpEntity;
		$this->httpRequest = $httpRequest;
		$this->user = $user;
		$this->serverAddress = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;
	}

	public function logVisit($url, $ip, $user_agent, $referer = null, $visited_user_id = null, $visited_event_id = null)
	{
		$method = null;
		if ($this->httpRequest->isMethod('GET')) {
			$method = 'GET';
		} else if ($this->httpRequest->isMethod('POST')) {
			$method = 'POST';
		} else if ($this->httpRequest->isMethod('PUT')) {
			$method = 'PUT';
		}

		$data = array(
			'url' => $url,
			'user_agent' => (!empty($user_agent) ? substr($user_agent, 0, 50) : null),
			'ajax_flag' => ($this->ajax == true ? 1 : 0),
			'referral' => (!empty($referer) ? substr($referer, 0, 50) : null),
			'http_method' => $method,
			'http_get' => $this->createQueryString($this->httpRequest->getQuery()),
			'http_post' => $this->createQueryString($this->httpRequest->getPost()),
			'server_ip' => $this->serverAddress,
			'remote_ip' => $ip,
			'ins_dt' => new \DateTime,
			'ins_process_id' => 'Logger::logVisit'
		);

		$this->lastLogVisitRow = $this->logVisitEntity->insert($data);
		return $this->lastLogVisitRow;
	}

    public function updateLogVisit($logId, $data) {
    	if (empty($logId)) {
    		throw new \Nette\InvalidArgumentException('Invalid log_visit id: ' . $logId);
    	}
        return $this->logVisitEntity->find($logId)
        	->update($data);
    }

    /**
     * Logs any type of message to database table log
     * @param  string $message
     * @param  string $type
     * @param  string $data
     * @return \Nette\Database\TableRow
     */
    public function log($message, $type = 'info', $param1 = '', $param2 = '', $param3 = '')
   	{
   		if ($message instanceof \Exception) {
   			return $this->logError($message);
   		}

   		$data = array(
			'message' => $message,
			'param1' => $param1,
			'param2' => $param2,
			'param3' => $param3,
			'type' => $type,
			'ins_dt' => new \DateTime,
			'ins_process_id' => 'Logger::log'
		);
		return $this->logEntity->insert($data);
   	}

   	/**
   	 * Converts array into query string
   	 * @param  array $arr
   	 * @return string
   	 */
    private function createQueryString(array $arr) {
    	if (empty($arr)) {
    		return null;
    	}

    	$ret = '?';
    	foreach ($arr as $k => $v) {
    		if ($ret <> '?') {
    			$ret .= '&';
    		}
    		$ret .= print_r($k, true) . '=' . print_r($v, true);
    	}

    	return $ret;
    }

	/**
	 * Logs error into database, URL is generated automatically
	 * @param string $lstErrorTpName
	 * @param string $message
	 * @param string $insProcessId
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function logError($message, $lstErrorTpName = null, $insProcessId = 'Logger::logError()'){

		$file_content = null;
		$now = new \DateTime();
		if ($message instanceof \Exception) {
			$lstErrorTpName = ($lstErrorTpName)?: 'exception';
			$exception = $message;
			$message = ($message instanceof Nette\FatalErrorException
				? 'Fatal error: ' . $exception->getMessage()
				: get_class($exception) . ": " . $exception->getMessage())
				. ($exception->getCode()? " #" . $exception->getCode() : '');

			// Check if this exception has been submitted recently
			$isReported = $this->logErrorEntity->getTable()
				->where("message", $message)
				->where("reported_flag", 0)
				->where("ins_dt >= ?", $now->modify('-3 hours'))
				->fetch();

			if (!$isReported) {
				$blueScreen = new ND\BlueScreen;
				ob_start();
				ob_start();
				$blueScreen->render($exception);
				$file_content = ob_get_contents();
				ob_end_clean();
				ob_end_clean();
			}
		} else {

			$isReported = $this->logErrorEntity->getTable()
				->where("message", $message)
				->where("reported_flag", 0)
				->where("url", $this->httpRequest->getUrl())
				->where("ins_dt >= ?", $now->modify('-3 hours'))
				->fetch();
		}

		$lstErrorTpName = ($lstErrorTpName)?: 'error';

		if ($isReported) {
			$isReportedArray = $isReported->toArray();
			$cnt = 1 + $isReportedArray['occured_cnt'];
			$isReported->update(array(
				"occured_cnt" => $cnt,
				"upd_process_id" => "REPEATED_EXCEPTION_OCCURENCE",
			));
			return $isReported;
		}

		$logVisitId = ($this->lastLogVisitRow ? $this->lastLogVisitRow->id : -1);

		$errorTp = $this->getErrorTpByName($lstErrorTpName);
		return $this->logErrorEntity->insert(array(
			"error_tp_id" => $errorTp->id,
			"message" => $message,
			"file_content" => $file_content,
			"url" => $this->httpRequest->getUrl(),
			"log_visit_id" => $logVisitId,
			"post_query" => $this->createQueryString($this->httpRequest->getPost()),
			"ins_dt" => new \DateTime,
			"ins_process_id" => $insProcessId,
		));
	}

	/**
	 * Logs unauthorized access into log table
	 * @return HQ\Logger
	 */
	public function logUnauthorizedAccess(){
		$this->log(
			"Unauthorized access!",
			"403",
			"User id: " . $this->user->id,
			$this->httpRequest->url
		);
		return $this;
	}

	/**
	 * Checks if current request URL contains /api/
	 * @return boolean
	 */
	private function _isApiCall() {
		if (strpos($this->httpRequest->getUrl(), '/api/') > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Tries to fetch Error type by name, if no row creates new one
	 * @param string $name
	 * @return \Nette\Database\Table\ActiveRow
	 */
	private function getErrorTpByName($name) {
		$row = $this->lstErrorTpEntity->findByName($name);
		if(!$row){
			$row = $this->lstErrorTpEntity
				->insert(array(
					"ins_process_id" => "Logger::getErrorTpByName()",
					"ins_dt" => new \DateTime,
					"name" => $name,
				));
		}
		return $row;
	}

}
