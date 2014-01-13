<?php

namespace HQ;

/**
 * Customization of default Nette\Security\User
 *
 * @author Josef Nevoral <josef.nevoral at gmail.com>
 */

class User extends \Nette\Security\User {

	public function hashPassword($password) {
		return sha1($password);
	}

}