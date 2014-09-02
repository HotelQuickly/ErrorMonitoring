<?php

namespace HQ\HipChat;

use HipChat\HipChat_Exception;

class HipChatProxy extends \Nette\Object
{

	private $active;
	private $room;
	private $sender;

	/** @var \HipChat\HipChat */
	private $hipChat;

	public function __construct(
		$active,
		$sender,
		$room,
		\HipChat\HipChat $hipChat
	) {
		$this->active = $active;
		$this->sender = $sender;
		$this->room = $room;
		$this->hipChat = $hipChat;
	}

	public function sendMessage($message)
	{
		if (!$this->active) {
			return;
		}

		$this->hipChat->set_verify_ssl(false);
		try {
			$this->hipChat->message_room($this->room, $this->sender, $message);
		} catch (HipChat_Exception $e) {
			if ($e->getCode() == 403) {
				// do nothing it's rate limit exceeded
			} else {
				throw $e;
			}
		}
	}

	public function setSender($sender)
	{
		$this->sender = $sender;
	}
}