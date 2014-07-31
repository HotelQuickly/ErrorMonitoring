<?php

namespace HQ\Model\Entity;

use Nette;

/**
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */
class ErrorReportingServiceRelEntity extends BaseEntity
{

	public function getNotEmailReportedErrors()
	{
		$query = '
			SELECT error.* from error
			INNER JOIN lst_error_status ON lst_error_status.id = error.error_status_id
				AND lst_error_status.code = "NEW"
			LEFT JOIN error_reporting_service_rel on error_reporting_service_rel.error_id = error.id
				AND error_reporting_service_rel.del_flag = 0
			LEFT JOIN lst_reporting_service ON error_reporting_service_rel.reporting_service_id = lst_reporting_service.id
				AND lst_reporting_service.del_flag = 0
				AND lst_reporting_service.code = "EMAIL"
			WHERE 1=1
				AND (error_reporting_service_rel.reported_flag = 0 OR error_reporting_service_rel.id IS NULL)
				AND error.del_flag = 0
		';

		return $this->getContext()->query($query)->fetchAll();
	}
}