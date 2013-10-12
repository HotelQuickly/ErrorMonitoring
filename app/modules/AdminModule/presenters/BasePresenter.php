<?php

namespace BackendModule\AdminModule;

use BackendModule;

use Nette\Security\User;

abstract class BasePresenter extends BackendModule\BasePresenter {

	/** @var \HotelQuickly\EmailBatch */
	protected $emailBatchService;

	/** @var \HotelQuickly\Notification */
	protected $notification;

	/** @var \HotelQuickly\EmailMessageFactory **/
	protected $spamEmailMessageFactory;

	/** @var \HotelQuickly\EmailMessageFactory **/
	protected $emailMessageFactory;

	protected $hotelsRelatedToUser = array();

	public function injectEmailBatchService(\HotelQuickly\EmailBatch $emailBatchService){
		$this->emailBatchService = $emailBatchService;
	}

	public function injectNotification(\HotelQuickly\Notification $notification){
		$this->notification = $notification;
	}

	public function injectSGridFactory(\HotelQuickly\SGridFactory $sendGridFactory){
		$this->emailMessageFactory = $sendGridFactory->real;
		$this->spamEmailMessageFactory = $sendGridFactory->fake;
	}

	public function beforeRender() {
		parent::beforeRender();
	}

	public function startup() {
		parent::startup();

		$this->template->hotelsRelatedToUser = array();
		$this->template->userHotelRel = array();
		if ($this->user->isLoggedIn()) {
			// get a list of hotels that is a user related to
			$this->hotelsRelatedToUser = array();
			$this->hotelsRelatedToUser = $this->models->userHotelRel->getTable()
				->select('hotel_id AS id, hotel.name')
				->where('user_id', $this->user->identity->id)
				->where('user_hotel_rel.del_flag', 0)
				->where('hotel.del_flag', 0);
			$this->template->hotelsRelatedToUser = $this->hotelsRelatedToUser;

			$this->template->userHotelRel = $this->models->userHotelRel->getTable()
				->where('user_id', $this->user->identity->id)
				->where('del_flag', 0);
		}

		// Redirect JOKE -- Tomas Laboutka
		if ($this->user->isLoggedIn() AND $this->user->identity->id == 25) {
			// $this->redirectUrl('http://www.hotelquickly.com/system/suspicious-activity-detected');
		}
	}

	public function handleSendBookingConfirmation($orderId, $recipient = '') {
		$this->user->authorizator->checkManagePrivilege('Backend:Admin:ExtendedBookingGrid');
		$order = $this->models->order->getTable()
			->find($orderId)
			->fetch();
		$user = $this->models->user->getTable()
			->find($order->user_id)
			->fetch();

		$this->template->notificationSent = true;

		// send booking confirmations
		$emailTemplate = $this->createEmailTemplate();
		$smsTemplate = $this->createSmsTemplate();
		$this->notification->setEmailTemplate($emailTemplate);
		$this->notification->setSmsTemplate($smsTemplate);

		if ($recipient == 'customer' OR empty($recipient)) {
			$this->notification->sendNotificationsToCustomer($user, $order);
		}

		if ($recipient == 'hotel' OR empty($recipient)) {
			$creditCard = $this->models->creditCard->getCreditCardForOrder($order->id, 'DB'); // get DEBET card
			$this->notification->sendNotificationsToHotel($order, $creditCard);
		}

		$this->flashMessage('Notifications were sent.');
		$this->redirect('this');
	}

	public function handleMarkAsConfirmedByCallCenter($orderId) {
		$order = $this->models->order->getTable()
			->find($orderId)
			->fetch();

		$this->models->logActivity
			->setActivity('CONFIRMORDER')
			->setUserId($this->user->id)
			->setHotelId($order->offer->hotel->id)
			->setOrderId($orderId)
			->log();

		$this->models->order->getTable()
			->find($orderId)
			->update(array(
				'cc_confirmed_flag' => 1,
				'upd_process_id' => 'BasePresenter::handleMarkAsConfirmedByCallCenter()'
			));

		$this->flashMessage('Booking was confirmed.');
		$this->redirect('this');
	}

	public function handleResendEmail($id)
	{
		$this->user->authorizator->checkManagePrivilege('Backend:Admin:Email');
		$email = $this->models->email->getTable()->get($id);
		if (!$email) {
			throw new \Nette\InvalidArgumentException('Email with ID '.$id.' does not exists!');
		}

		try {
			// insert email into database
			$emailData = $email->toArray();
			unset($emailData['id']);
			$emailData['sent_dt'] = new \DateTime();
			$emailData['ins_dt'] = new \DateTime();
			$emailData['ins_process_id'] = 'EmailPresenter::resendEmail()';

			$email = $this->models->email->getTable()
				->insert($emailData);

			$message = $this->emailMessageFactory->create($emailData);
			$this->emailMessageFactory->send($message);
		} catch (\Exception $e) {
			$this->logger->logError($e);
			$email->update(array(
				'sent_dt' => null,
				'upd_process_id' => 'EmailPresenter::resendEmail() catch error'
			));
		}

		$this->flashMessage('Email successfully send.');
		$this->invalidateControl('emails');
	}

}
