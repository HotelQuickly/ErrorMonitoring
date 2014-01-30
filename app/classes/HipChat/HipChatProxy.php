<?php

namespace HQ\HipChat;

class HipChatProxy extends \Nette\Object {

	private $room;
	private $sender;

	/** @var \HipChat\HipChat */
	private $hipChat;

	public function __construct(\HipChat\HipChat $hipChat, $sender, $room) {
		$this->hipChat = $hipChat;
		$this->sender = $sender;
		$this->room = $room;
	}

	public function sendMessage($message) {
		$this->hipChat->set_verify_ssl(false);
		$this->hipChat->message_room($this->room, $this->sender, $message);
	}

	public function setSender($sender) {
		$this->sender = $sender;
	}
}