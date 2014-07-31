<?php

namespace HQ;

class LogCron extends \Nette\Object {

	private $httpRequest;
	private $logger;

	public $productionMode;

	private $cronRow;
	private $logCronRow;
	private $debugOutput;

	/** @var boolean */
	private $successfulFlag = false;
	/** @var boolean */
	private $skippedFlag = false;
	/** @var string JSON */
	private $returnValue = null;

	const DAILY_CRON_REPORT_TASKNAME = '/cron/report/send-mail';
	const IMPORTANT_CRON_CHECK_TASKNAME = '/cron/report/check-important-tasks';

	/** @var \HQ\Model\Entity\LogCronEntity */
	private $logCronEntity;

	/** @var \HQ\Model\Entity\CronEntity */
	private $cronEntity;

	/** @var \HQ\Model\Entity\LogErrorLogCronRelEntity */
	private $logErrorLogCronRelEntity;

	public function __construct(
		$productionMode,
		\HQ\Model\Entity\LogCronEntity $logCronEntity,
		\HQ\Model\Entity\CronEntity $cronEntity,
		\HQ\Model\Entity\LogErrorLogCronRelEntity $logErrorLogCronRelEntity,
		\HQ\Logger $logger,
		\Nette\Http\Request $httpRequest
	) {
		$this->logger = $logger;
		$this->productionMode = $productionMode;
		$this->httpRequest = $httpRequest;
		$this->logCronEntity = $logCronEntity;
		$this->cronEntity = $cronEntity;
		$this->logErrorLogCronRelEntity = $logErrorLogCronRelEntity;
	}

	/**
	 * Returns current cron task ActiveRow, $this->cronRow is set in __construct()
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function getCronRow(){
		if(!$this->cronRow) {
			$this->cronRow = $this->getTaskByUrl( $this->httpRequest->getUrl() );
		}
		return $this->cronRow;
	}

	public function getTaskNameByUrl($url){
		$urlObj = new \Nette\Http\Url( $url );
		return $urlObj->getBasePath() . $urlObj->getRelativeUrl();
	}

	/**
	 * Finds row with the $url parameter, if not found it will create a new one
	 * @param type $url
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function getTaskByUrl($url){
		$task = $this->getTaskNameByUrl($url);
		$row = $this->cronEntity->getTable()
			->select('cron.*')
			->where("task LIKE ?", $task)
			->where("del_flag", 0)
			->fetch();
		if($row) {
			return $row;
		}
		else{
			$task_time = null;
			return $this->cronEntity->insert(array(
				"task" => $task,
				"time" => $task_time,
				"alias" => $this->getAlias($task),
				"del_flag" => 0,
				"ins_process_id" => "HQ\LogCron::getTaskByUrl()",
				"ins_dt" => new \DateTime,
			));
		}
	}

	/**
	 * Creates new row in log_cron and returns inserted row or false in case of error
	 * @param int $cron_id
	 * @return \Nette\Database\Table\ActiveRow | FALSE
	 */
	public function createLogFromCronId($cronId = -1){
		$row = $this->logCronEntity->insert(array(
			"reported_flag" => 0,
			"manual_flag" => ($this->isManualCall())? 1 : 0,
			"start_time" => new \DateTime,
			"ins_dt" => new \DateTime,
			"ins_process_id" => "HQ\\LogCron::createLogFromCronId($cronId)",
			"cron_id" => $cronId,
		));
		$this->logCronRow = $row;
		return $row;
	}

	/**
	 * Compares remote and server IP and determines if it is manual call or not
	 * @return boolean
	 */
	public function isManualCall(){
		if( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'wget') === false ) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Sets the successful flag
	 * @param boolean $bool
	 * @return \HQ\LogCron
	 */
	public function setSuccessfulFlag($bool){
		$this->successfulFlag = $bool;
		return $this;
	}

	/**
	 * Checks if cron did run successfully
	 * @return boolean
	 */
	public function getSuccessfulFlag(){
		return $this->successfulFlag;
	}

	/**
	 * Sets return values to JSON encoded array or NULL if empty array given
	 * @param array $values
	 * @return \HQ\LogCron
	 */
	public function setReturnValue(array $values = array() ){
		if(empty($values)){
			$this->returnValue = null;
		}
		else{
			$this->returnValue = \Nette\Utils\Json::encode($values);
		}
		return $this;
	}

	/**
	 * Gets cron return values as JSON encoded array
	 * @return string JSON
	 */
	public function getReturnValue(){
		return $this->returnValue;
	}

	/**
	 * Sets debug output to $html, and prepares it for later rendering
	 * @param mixed $html
	 * @return \HQ\LogCron
	 */
	public function setDebugOutput($html){
		$this->debugOutput = $html;
		return $this;
	}

	/**
	 * Renders the set output
	 */
	public function renderDebugOutput(){
		print_r($this->debugOutput);
	}

	/**
	 * Reports the error into log_error table and adds relation into log_error_log_cron_rel table
	 * @param string $message
	 * @param int $logCronId
	 * @param string $insProcessId
	 * @return \HQ\LogCron
	 */
	public function logError($message, $logCronId = -1, $insProcessId = 'HQ\\LogCron::logError()'){
		$logErrorRow = $this->logger->logError($message, 'cron', $insProcessId);
		$this->logErrorLogCronRelEntity->getTable()->insert(array(
			"log_cron_id" => ($logCronId > 0)? $logCronId : $this->logCronRow->id,
			"log_error_id" => $logErrorRow->id,
			"ins_dt" => new \DateTime,
			"ins_process_id" => $insProcessId,
		));
		return $this;
	}

	/**
	 * Sets reported_flag=1 for all log_cron.id given in parameter
	 * @param array $logCronsIds
	 * @return int | FALSE
	 */
	public function setCronsReported(array $logCronIds = array() ){
		return $this->logCronEntity->getTable()
			->where("del_flag", 0)
			->where("id", $logCronIds)
			->update(array(
				"reported_flag" => 1
			));
	}

	/**
	 * Finds errors from log_error to selected $cronId and limited with $limit (if set)
	 * @param int $cronId
	 * @param int $limit
	 * @return \Nette\Database\Table\Selection
	 */
	public function getNonReportedErrorsByCronId($cronId, $limit = null){
		$logCrons = $this->logCronEntity->getTable()
			->where("del_flag", 0)
			->where("reported_flag", 0)
			->where("cron_id", $cronId)
			->order("id DESC")
			->select("id AS log_cron_id");
		$rows = $this->logErrorLogCronRelEntity->getTable()
			->where("log_cron_id", $logCrons)
			->where("log_error.reported_flag", 0)
			->select("log_error.message, log_error.id, log_error.ins_dt");
		if($limit) {
			$rows->limit($limit);
		}
		return $rows;
	}

	/**
	 * Check if there were any errors in given period of DateTimes
	 * @param \DateTime $dateFrom
	 * @param \DateTime $dateTo
	 * @param int $cronId
	 * @return boolean
	 */
	public function hasErrorsInPeriod($dateFrom, $dateTo, $cronId = null){
		$errorsCount = $this->getErrorsInPeriod($dateFrom, $dateTo, $cronId)->count('*');
		return ($errorsCount)? true : false;
	}

	/**
	 * Get errors in given period for given cron id
	 * @param \DateTime $dateFrom
	 * @param \DateTime $dateTo
	 * @param int $cronId
	 * @return \Nette\Database\Table\Selection
	 */
	public function getErrorsInPeriod($dateFrom, $dateTo, $cronId = null){
		if(!$cronId){
			$cronId = $this->logCronRow->id;
		}
		$logCrons = $this->logCronEntity->getTable()
			->where("ins_dt <= ?", $dateTo)
			->where("ins_dt >= ?", $dateFrom)
			->where("cron_id", $cronId)
			->where("del_flag", 0)
			->select("id AS log_cron_id");
		$errors = $this->logErrorLogCronRelEntity->getTable()
			->where("del_flag", 0)
			->where("log_cron_id", $logCrons);
		return $errors;
	}

	/**
	 * Creates readable string from filename
	 * @param string $taskName
	 * @return string
	 */
	public function getAlias($taskName){
		if( ($pos = strrpos($taskName, "/")) === false){
			return;
		}
		$string = str_replace("-", " ", substr($taskName, $pos+1));
		if( ($questPos = strpos($string, "?")) ) {
			$string = substr($string, 0, $questPos);
		}
		return ucfirst($string);
	}

	/**
	 * Sets the skipped flag
	 * @param boolean $bool
	 * @return \HQ\LogCron
	 */
	public function setSkippedFlag($bool)
	{
		$this->skippedFlag = $bool;
		return $this;
	}

	/**
	 * Checks if cron was skipped
	 * @return boolean
	 */
	public function getSkippedFlag(){
		return $this->skippedFlag;
	}


	/**
	 * Saves cron log and terminates presenter
	 * @throws \Nette\Application\AbortException
	 */
	public function finishTask()
	{
		// Stores the output
		$output = (ob_get_length())? ob_get_contents() : null;
		// If on development or manual call - output + debug output are rendered
		if($this->productionMode && !$this->isManualCall()){
			if (ob_get_contents() && ob_get_length()) {
				@ob_end_clean();
			}
		} else {
			while (@ob_end_flush());
			$this->renderDebugOutput();
		}

		if ($this->logCronRow) {
			$updated = $this->logCronRow->update(array(
				"finish_time" => new \DateTime,
				"output" => $output,
				"return_value" => $this->getReturnValue(),
				"successful_flag" => $this->getSuccessfulFlag(),
				"skipped_flag" => $this->getSkippedFlag(),
				"upd_process_id" => "LogCron::finishTask()",
			));
			if(!$updated){
				$this->logError('Unable to update row in finishTask() method');
			}
		}

		if (!$this->getSkippedFlag()) {
			$this->cronRow->update(array(
				'running_flag' => 0
			));
		}
		throw new \Nette\Application\AbortException(); // Same as $presenter->terminate();
	}

}
