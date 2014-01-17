<?php

namespace HQ\Model\Entity;

class ErrorEntity extends BaseEntity {

	public function solve($id) {
		return $this->find($id)
			->update(array(
				"solved_flag" => 1,
				"upd_process_id" => __METHOD__
			));
	}
}