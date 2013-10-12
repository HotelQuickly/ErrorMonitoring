<?php

namespace BackendModule\AdminModule;

use Nette\Image;
use \Nette\Application\UI\Form;
/**
 * Description of Homepage
 *
 */

class HomepagePresenter extends BasePresenter {

	/** @autowire @var \HotelQuickly\RoomAvailability\RoomAvailabilityValidator */
	protected $roomAvailabilityValidator;

	/** @persistent int **/
	public $hotelId;

	public $activeDate;

	private $taskQueue;

	/** @autowire @var \Models\Room */
	protected $roomModel;

	/** @autowire @var \HotelQuickly\ThirdPartyService */
	protected $thirdPartyService;

	/*
	 */
	public function injectTaskQueue(\HotelQuickly\TaskQueue $taskQueue)
	{
		$this->taskQueue = $taskQueue;
	}

	public function actionDefault(){

		$this->template->currentHotel = null;

		$this->activeDate = Date('Y-m-d');

		$userRole = $this->models->lstUserRole->getTable()
			->where("name", $this->user->roles[0])
			->fetch();

		if($userRole->starting_page){
			$this->redirect($userRole->starting_page);
		}

		$userHotelRel = $this->models->userHotelRel->getTable()
			->where('user_id', $this->user->identity->id)
			->where('del_flag', 0)
			->select('hotel_id AS id');

		if(!$userHotelRel->count('*')){
			$this->redirect(':Backend:Crm:Hotel:noHotelAssignedYet');
		}
	}

	public function renderDefault()
	{
		$this->template->bookingsGridData = $this->models->order->getTable()
			->where("order.del_flag", 0)
			->where('offer.hotel_id', $this->hotelsRelatedToUser)
			->where('DATE(offer.checkin_date) = DATE(NOW())');
	}

	public function actionCalendar(){
		$userHotelRel = $this->models->userHotelRel->getTable()
			->where('user_id', $this->user->identity->id)
			->where('del_flag', 0)
			->select('hotel_id AS id');

		if(!$userHotelRel->count('*')){
			$this->redirect(':Backend:Crm:Hotel:noHotelAssignedYet');
		}
	}

	public function handleSwitchTabOnDashboard($hotelId) {

		$hotel = $this->models->hotel->getTable()
			->find($hotelId)
			->fetch();

		if ($hotel) {
			$this->hotelId = $hotel->id;
		}
	}

	public function createComponentRoomAvailability()
	{
		$component = new \HotelQuickly\Components\RoomAvailabilityControl(
				$this->models,
				$this->hotelsRelatedToUser,
				$this->context,
				$this->logger,
				$this->settings,
				$this->taskQueue,
				$this->roomModel,
				$this->thirdPartyService,
				$this->roomAvailabilityValidator
			);
		if($this->hotelId) {
			$component->setHotelId($this->hotelId);
		}
		return $component;
	}

	public function createComponentRoomRegularPrices()
	{
		$component =  new Components\RoomRegularPricesControl(
				$this->models,
				$this->hotelsRelatedToUser,
				$this->context->roomPricesHandler,
				$this->logger
			);
		if($this->hotelId) {
			$component->setHotelId($this->hotelId);
		}
		return $component;
	}

}
