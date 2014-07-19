<?php


namespace HQ\Security;

/**
 * Class PasswordService
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */
class PasswordService extends \Nette\Object
{
	public function hash($password)
	{
		return sha1($password);
	}
}