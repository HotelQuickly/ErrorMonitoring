<?php

use Nette\Security as NS;
use HQ\Model\Entity;


/**
 * User authenticator.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class Authenticator extends Nette\Object implements NS\IAuthenticator
{
	/** @var Entity\UserEntity */
	private $userEntity;

	public function __construct(Entity\UserEntity $userEntity) {
		$this->userEntity = $userEntity;
	}

	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials, $facebookLogin = false, $mobileLogin = false)
	{
		if ($mobileLogin == true) {
            list($secretKey) = $credentials;

        	$row = $this->userEntity->getTable()->where('app_secret_key', $secretKey)->fetch();

			if (!$row) {
				throw new NS\AuthenticationException("User with app secret key '$secretKey' not found (Mobile app Login).", self::IDENTITY_NOT_FOUND);
			}
			return new NS\Identity($row->id, $row->user_role->name, $row->toArray());
		} else if ($facebookLogin == true) {
            list($fbUid) = $credentials;

        	$row = $this->userEntity->where('fb_uid', $fbUid)->fetch();

			if (!$row) {
				throw new NS\AuthenticationException("User with ID '$fbUid' not found (Facebook Login).", self::IDENTITY_NOT_FOUND);
			}
			return new NS\Identity($row->id, $row->user_role->name, $row->toArray());
		} else {
            list($username, $password) = $credentials;

        	$row = $this->userEntity->where('email', $username)->select('user.*, user_role.name AS user_role_name')->fetch();

			if (!$row) {
				throw new NS\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
			}

			unset($row->pass);
			return new NS\Identity($row->id, $row->user_role->name, $row->toArray());
		}
	}

}
