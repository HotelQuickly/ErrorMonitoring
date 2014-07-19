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

	/** @var \HQ\Security\PasswordService */
	private $passwordService;

	public function __construct(
		Entity\UserEntity $userEntity,
		\HQ\Security\PasswordService $passwordService
	) {
		$this->userEntity = $userEntity;
		$this->passwordService = $passwordService;
	}

	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$row = $this->userEntity->findOneBy(array('email' => $username));

		if (!$row) {
			throw new NS\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
		}

		if ($row->password != $this->passwordService->hash($password)) {
			throw new NS\AuthenticationException("Wrong password or email.", self::INVALID_CREDENTIAL);
		}

		return new NS\Identity($row->id, null, $row->toArray());
	}

}
