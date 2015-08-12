<?php

namespace HQ\Model\Entity;

class ErrorEntity extends BaseEntity {

	public function archiveAll()
	{
		return $this->findBy(array(
			'error_status_id' => 1
		))->update(array(
			'error_status_id' => 2,
			"upd_process_id" => __METHOD__
		));
	}

	public function archive($id)
	{
		return $this->find($id)
			->update(array(
				"error_status_id" => 2,
				"upd_process_id" => __METHOD__
			));
	}

	public function unarchive($id)
	{
		return $this->find($id)
			->update(array(
				"error_status_id" => 1,
				"upd_process_id" => __METHOD__
			));
	}

}