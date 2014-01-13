<?php

namespace HotelQuickly\Factory\Grid;

use Nette,
	Nette\Database\Table,
	Models,
	BackendModule\AdminModule\Components\BlacklistGrid;

class BlacklistGridFactory extends Nette\Object {

	public function create(Table\Selection $blacklistData)
	{
		return new BlacklistGrid($blacklistData);
	}

}
