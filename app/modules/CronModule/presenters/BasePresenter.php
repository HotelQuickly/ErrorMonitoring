<?php

namespace CronModule;

/**
 * Base presenter for cron module
 * @author Josef Nevoral
 */
class BasePresenter extends \BasePresenter
{
	/** @autowire @var \HotelQuickly\LogCron  */
	protected $logCron;

	/** @var \Nette\Database\Table\ActiveRow */
	protected $logCronRow;

	/** @var \Nette\Database\Table\ActiveRow */
	protected $cronRow;



	public function startup() {
		parent::startup();
		$this->cronRow = $this->logCron->getCronRow();

		if( !is_object($this->cronRow) || $this->cronRow->id < 1) {
			$e = new \Nette\InvalidArgumentException("Uknown cron detected!");
			$this->logCron->logError($e);
			$this->logCron->finishTask();
		} else {
			$this->logCronRow = $this->logCron->createLogFromCronId( $this->cronRow->id );
		}

		$cronRow = $this->cronRow;
		if ($this->cronRow
			&& isset($this->cronRow->running_flag)
			&& $this->cronRow->running_flag == 1
			&& $this->context->params['productionMode'] // in development always run the cron
		) {
			$now = new \DateTime;
			$diff = $now->diff($this->cronRow->upd_dt);

			// if cron was started more than 20 minutes ago, run again. There is big change, there was some error in cron which prevent finishing
			if ($diff->d > 0
				|| $diff->h > 0
				|| $diff->i > 20
			) {
				$this->cronRow->update(array(
					'running_flag' => 0,
					'upd_process_id' => 'BasePresenter::startup()'
				));
			} else {
				// if cron is requested when previous request is still running, do not allow to run again
				$this->logCron->setSkippedFlag(true);
				$this->logCron->finishTask();
			}
		}

		$this->cronRow->update(array(
			'running_flag' => 1
		));

		// set NewRelic background Job
		// If no argument or true as an argument is given, mark the current transaction as a background job.
		// If false is passed as an argument, mark the transaction as a web transaction.
		if (extension_loaded('newrelic')) {
			newrelic_background_job(1);
		}


		// Check for same tasks in db in interval
		$dateCheck = new \DateTime;
		$dateCheck->add( \DateInterval::createFromDateString('-30 seconds') );
		$multipleCheck = $this->models->logCron->getTable()
			->where("del_flag", 0)
			->where("cron_id", $this->cronRow->id)
			->where("ins_dt >= ?", $dateCheck)
			->count('*');

		// Same task run already in period, terminate()!
		$multipleCheck = 0;
		if($multipleCheck > 0) {
			$this->logCron->setSkippedFlag(true);
			$this->logCron->finishTask();
		}
	}

	/**
	 * Cron jobs are executed on domain emails.hotelquickly.com
	 * But we want to have www.hotelquickly.com in all links
	 * @param  [type] $destination [description]
	 * @param  array  $args        [description]
	 * @return [type]              [description]
	 */
	public function link($destination, $args = array())
 	{
 		$link = parent::link($destination, $args);
 		$link = str_replace(array(
				'emails.hotelquickly.com',
				'hqcron.hotelquickly.com',
				'hqtest.hotelquickly.com',
				'cron.hotelquickly.com',
			), 'www.hotelquickly.com', $link
		);
 		return $link;
 	}

	public function beforeRender() {
		$this->logCron->finishTask();
	}

}
