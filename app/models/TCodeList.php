<?php

namespace HQ\Model\Entity;

/**
 *  Trait for list tables, allowing them to use functions for their specific columns
 *
 *  @author Jan Mikes <jan.mikes@hotelquickly.com>
 *  @copyright HotelQuickly Ltd.
 */
trait TCodeList {

	/**
	 * @param  string $name
	 * @return Nette\Database\Table\ActiveRow|FALSE
	 */
	public function findByName($name)
	{
		return $this->findOneBy(array(
			"name" => $name,
		));
	}


	/**
	 * @param  string $name
	 * @return Nette\Database\Table\ActiveRow|FALSE
	 */
	public function findByCode($code)
	{
		return $this->findOneBy(array(
			"name" => $code,
		));
	}

}
