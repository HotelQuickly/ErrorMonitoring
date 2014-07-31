<?php

namespace HQ\HipChat;

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
		$this->hipChat->message_room($this->room, $this->sender, $message);
	}

	public function setSender($sender)
	{
		$this->sender = $sender;
	}
}