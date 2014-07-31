<?php


namespace HQ\Mail;

/**
 * Class IMailMessageFactory
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */
interface IMailMessageFactory
{
	/** @return \Nette\Mail\Message */
	public function create();
} 