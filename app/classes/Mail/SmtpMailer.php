<?php

namespace HQ\Mail;

/**
 * Custom Smtp mailer
 * It send's emails only on production development if not stated differently
 *
 * @author Josef Nevoral <josef.nevoral@gmail.com>
 */
class SmtpMailer extends \Nette\Mail\SmtpMailer {

	/** @var bool */
	private $productionMode;

	/** @var bool */
	private $sendEmailInDevelopment;

	/**
	 * Basic setting
	 * @param bool  $productionMode
	 * @param bool  $sendEmailInDevelopment
	 */
	public function __construct(
		$productionMode,
		$sendEmailInDevelopment
	) {
		parent::__construct();
		$this->productionMode = $productionMode;
		$this->sendEmailInDevelopment = $sendEmailInDevelopment;
	}

	/**
	 * Checks if is not in production mode
	 * If is in production sends email, else does not
	 * @param  Message
	 * @return void
	 */
	public function send(\Nette\Mail\Message $mail)
	{
		if ($this->productionMode || $this->sendEmailInDevelopment) {
			parent::send($mail);
		}
	}

}

?>
