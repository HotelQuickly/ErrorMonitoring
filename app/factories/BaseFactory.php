<?php

namespace HotelQuickly\Factory;

use Nette,
	HotelQuickly as HQ;

/**
 *  @author Jan Mikes <jan.mikes@hotelquickly.com>
 *  @copyright HotelQuickly Ltd.
 */
class BaseFactory extends Nette\Object implements IControlFactory {

	/** @var HotelQuickly\Template\TempalteFactory */
	protected $templateFactory;

	public function setTemplateFactory(HQ\Template\TemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}

}
