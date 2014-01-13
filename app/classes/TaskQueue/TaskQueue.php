<?php

namespace HQ;

use HQ\Model\Entity;

class TaskQueue extends \Nette\Object {

	const PRIORITY_DEFAULT = 10000; // priority range 0 to 2^32, 0 = most important
	const RUN_TIMEOUT_DEFAULT = 600; // will kill job if reach this timeout

	protected $tube_name;
	protected $beanstalk;
	protected $priority;
	protected $start_delay;
	protected $run_timeout;
	protected $is_connected;
	protected $worker_checker_url;

	/** @var Entity\LogTaskQueue */
	private $logTaskQueue;

	public function __construct(
		$tube_name,
		$priority=PRIORITY_DEFAULT,
		$run_timeout=RUN_TIMEOUT_DEFAULT,
		$worker_checker_url,
		$start_delay=0,
		Entity\LogTaskQueue $logTaskQueue,
		\Socket_Beanstalk $injbeanstalk
	) {
		$this->beanstalk = $injbeanstalk;
		$this->tube_name = $tube_name;
		$this->priority = $priority;
		$this->start_delay = $start_delay;
		$this->run_timeout = $run_timeout;
		$this->is_connected = false;
		$this->worker_checker_url = $worker_checker_url;
	}

	public function __destruct() {
		$this->beanstalk->disconnect();
	}

	// TODO: add setter priorty, run_timeout, start_delay

	/**
	 * Insert task queue to beanstalk which will be process immediately on cron server if  queue is empty.
	 *
	 * @param $task_name: string should be string of TaskModule "[presenter]/[action]"
	 * which'll be called by curl http://xxx/task/<task_name>
	 * example: offer/updateroomavailibity
	 * @param $param: array param sent to worker by json_encode
	 * @return void
	 */
	public function insert($task_name=null, $param=null, $ins_process_id="TaskQueue") { // we'll pass param as json_encode to beanstalk
		$this->connect();

		$logtask = $this->logTaskQueueEntity->getTable()->insert(array(
				"del_flag" => 0,
				"task_name" => $task_name,
				"param" => json_encode($param),
				"ins_dt" => new \DateTime,
				"ins_process_id" => $ins_process_id
		), true);

		$logtask_id = $logtask->id;

		$param_to_send = array();
		$param_to_send['task_name'] = $task_name;
		$param_to_send['id_logtask'] = $logtask_id;
		$param_to_send['param'] = $param;
		$json_body = json_encode($param_to_send);

		$this->beanstalk->put(
				$this->priority, // priority
				$this->start_delay,  // wait to put job into the ready queue.
				$this->run_timeout, // Give seconds the job to run.
				$json_body // The job's body.
		);
	}

	protected function connect() {
		if(!$this->is_connected) {
			if(!$this->beanstalk->connect()) {
				throw new InvalidStateException("Cannot connect to beanstalkd! please check the service. task queue won't insert.");
			}
			$this->beanstalk->choose($this->tube_name); // select tube
			$this->is_connected = true;
		}
	}

	public function test_connection() {
		if($this->is_connected) {
			$ret = $this->beanstalk->connect();
			return $ret;
		} else {
			$ret = $this->beanstalk->connect();
			$this->beanstalk->disconnect();
			return $ret;
		}
	}

	public function stats() {
		$this->connect();

		return $this->beanstalk->stats();
	}

	public function is_queue_worker_running() {
		if($this->worker_checker_url && $this->worker_checker_url != '') {
			$resp = file_get_contents($this->worker_checker_url);
			$json = json_decode($resp);
			if(isset($json->status) && $json->status === true) {
				return true;
			}
		}
		return false;
	}
}
