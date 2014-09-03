<?php


namespace HQ\Reporting;
use HQ\Mail\IMailMessageFactory;
use HQ\Mail\SmtpMailer;
use HQ\Model\Entity\ErrorReportingServiceRelEntity;
use HQ\Model\Entity\LstReportingServiceEntity;
use HQ\Template\TemplateFactory;
use Nette\Application\UI\Presenter;

/**
 * Class EmailReporter
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */
class EmailReporter extends \Nette\Object
{
	/** @var  bool */
	private $activated;

	/** @var array  */
	private $emailHeaders;

	/** @var \HQ\Mail\IMailMessageFactory  */
	private $mailMessageFactory;

	/** @var \HQ\Mail\SmtpMailer  */
	private $mailer;

	/** @var \HQ\Model\Entity\ErrorReportingServiceRelEntity  */
	private $errorReportingServiceRelEntity;

	/** @var \HQ\Template\TemplateFactory  */
	private $templateFactory;

	/** @var \HQ\Model\Entity\LstReportingServiceEntity  */
	private $lstReportingServiceEntity;

	public function __construct(
		$activated,
		array $emailHeaders,
		IMailMessageFactory $mailMessageFactory,
		SmtpMailer $mailer,
		ErrorReportingServiceRelEntity $errorReportingServiceRelEntity,
		TemplateFactory $templateFactory,
		LstReportingServiceEntity $lstReportingServiceEntity
	) {
		$this->activated = $activated;
		$this->emailHeaders = $emailHeaders;
		$this->mailMessageFactory = $mailMessageFactory;
		$this->mailer = $mailer;
		$this->errorReportingServiceRelEntity = $errorReportingServiceRelEntity;
		$this->templateFactory = $templateFactory;
		$this->lstReportingServiceEntity = $lstReportingServiceEntity;
	}

	public function sendReport(Presenter $presenter)
	{
		if (!$this->activated) {
			return;
		}

		$notReportedErrors = $this->errorReportingServiceRelEntity->getNotEmailReportedErrors();
		if (empty($notReportedErrors)) {
			return;
		}

		$template = $this->templateFactory->createTemplate($presenter, '/templates/Email/errorReport.latte');
		$template->errors = $notReportedErrors;

		$mail = $this->mailMessageFactory->create();
		$mail->setFrom($this->emailHeaders['from'])
			->addTo($this->emailHeaders['to'])
			->setSubject($this->emailHeaders['subject'] . ' ' . (new \DateTime)->format('Y-m-d H:i'))
			->setHtmlBody($template);

		$this->mailer->send($mail);

		$this->markErrorsAsReported($notReportedErrors, 'EMAIL');
	}


	public function markErrorsAsReported(array $errors, $reportingServiceCode)
	{
		$reportingService = $this->lstReportingServiceEntity->findByCode($reportingServiceCode);

		foreach ($errors as $error) {
			$this->errorReportingServiceRelEntity->insertOrUpdate(array(
				'error_id' => $error->id,
				'reporting_service_id' => $reportingService->id,
				'reported_flag' => 1,
				'ins_dt' => new \DateTime
			));
		}
	}
}