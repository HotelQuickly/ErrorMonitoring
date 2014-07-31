<?php


namespace CronModule;

/**
 * Class ReportPresenter
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */
class ReportPresenter extends BasePresenter
{

	/** @autowire
	 * @var  \HQ\Reporting\EmailReporter */
	protected $emailReporter;

	public function actionSendEmailReport()
	{
		$this->emailReporter->sendReport($this);
	}
} 