<?php

namespace HQ\Security;

use Nette\Security\Permission,
	Models,
	Nette;


class Acl extends Permission implements \Nette\Security\IAuthorizator
{

	/** @var \Models\Base */
	private $lstUserRole;

	/** @var \Models\Base */
	private $lstAclResource;

	/** @var \Models\Base */
	private $userRoleUserRoleRel;

	/** @var \Models\Base */
	private $resourceUserRoleRel;

	/** @var HQ\Logger */
	private $logger;

	/** @var array */
	private $allRoles = array();

	/** @var array */
	private $rolesRels = array();

	/** @var Nette\Security\User */
	private $user;

	public function __construct(
		Nette\Security\User $user,
		Models\Base $lstUserRole,
		Models\Base $userRoleUserRoleRel,
		Models\Base $lstAclResource,
		Models\Base $resourceUserRoleRel,
		HQ\Logger $logger)
    {
		$this->lstUserRole = $lstUserRole;
		$this->lstAclResource = $lstAclResource;
		$this->userRoleUserRoleRel = $userRoleUserRoleRel;
		$this->resourceUserRoleRel = $resourceUserRoleRel;
		$this->logger = $logger;
		$this->user = $user;

		$this->allRoles = $lstUserRole->getTable()->fetchPairs("name");
		foreach( $userRoleUserRoleRel->getTable()->where("del_flag", 0) as $rel ) {
			$this->rolesRels[$rel->user_role->name][] = $rel->parent_role->name;
		}

		// Load roles from db
		$userRoles = $lstUserRole->getTable()
			->where("del_flag", 0)
			->where("id > ?", 0);
		foreach($userRoles as $role){
			// Parents are loaded automatically
			$this->addRole($role->name);
		}

		// Load resources from db
		$resources = $lstAclResource->getTable()
			->where("del_flag", 0)
			->where("id > ?", 0);
		foreach($resources as $resource){
			$this->addResource($resource->name);
		}


		$permissions = $this->resourceUserRoleRel->getTable()
			->where("del_flag", 0);

		foreach($permissions as $permission){
			$role = $permission->user_role->name;
			$resource = $permission->acl_resource->name;
			$privilegeCode = $permission->privilege->code;

			switch($privilegeCode){
				case 'ALL':
					$privilege = self::ALL;
					break;

				case 'VIEW':
					$privilege = 'view';
					break;

				case 'MANAGE':
					$privilege = array('view', 'manage');
					break;

				default:
					$privilege = self::DENY;
					break;
			}

			if($permission->allowed_flag == 1){
				$this->allow($role, $resource, $privilege);
			}
			else {
				$this->deny($role, $resource, $privilege);
			}
		}

		$this->allow("admin");
    }

	/**
	 * If $resource is not defined, creates new one
	 * For more info see \Nette\Security\Permission::isAllowed doc
	 */
	public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL) {
		if($resource != self::ALL && !$this->hasResource($resource)){
			$this->addResourceToDb($resource);
			$this->addResource($resource);
		}
		return parent::isAllowed($role, $resource, $privilege);
	}

	/**
	 * If $resource is not defined, creates new one (for each if is array)
	 * For more info see \Nette\Security\Permission::allow doc
	 */
	public function allow($roles = self::ALL, $resources = self::ALL, $privileges = self::ALL, $assertion = NULL) {
		if($resources != self::ALL){
			if(!is_array($resources)){
				$resources = array($resources);
			}
			foreach($resources as $resource){
				if($resource != self::ALL && !$this->hasResource($resource)){
					$this->addResourceToDb($resource);
					$this->addResource($resource);
				}
			}
		}
		return parent::allow($roles, $resources, $privileges, $assertion);
	}

	/**
	 * If $resource is not defined, creates new one (for each if is array)
	 * For more info see \Nette\Security\Permission::deny doc
	 */
	public function deny($roles = self::ALL, $resources = self::ALL, $privileges = self::ALL, $assertion = NULL) {
		if($resources != self::ALL){
			if(!is_array($resources)){
				$resources = array($resources);
			}
			foreach($resources as $resource){
				if($resource != self::ALL && !$this->hasResource($resource)){
					$this->addResourceToDb($resource);
					$this->addResource($resource);
				}
			}
		}
		return parent::deny($roles, $resources, $privileges, $assertion);
	}

	/**
	 * Adds unexisting resource to db
	 * @param string $resource
	 * @return \Nette\Database\Table\ActiveRow
	 */
	private function addResourceToDb($resource){
		return $this->lstAclResource->insertIgnore(array(
				"name" => $resource,
				"ins_dt" => new \DateTime,
				"ins_process_id" => 'HQ\\Acl::isAllowed()',
			));
	}

	/**
	 * Helping function to add roles from database, for roles which parents was not defined yet
	 * @param string $role
	 * @param mixed $parent
	 */
	public function addRole($role, $parents = null){
		if($this->hasRole($role)) {
			return $this;
		}
		$parents = array();
		if(isset($this->rolesRels[$role]) && is_array($this->rolesRels[$role])){
			foreach($this->rolesRels[$role] as $parent){
				if(!$this->hasRole($parent)){
					$this->addRole($parent);
				}
				$parents[$role] = $parent;
			}
		}
		else {
			$parents[$role] = null;
		}
		return parent::addRole($role, isset($parents[$role])? $parents[$role] : null);
	}

	/**
	 * If user is not allowed to manage resource $resource throws exception otherwise returns true
	 * @param  string $resource
	 * @return true
	 * @throws HQ\UnauthorizedAccess
	 */
	public function checkManagePrivilege($resource){
		if(empty($this->user->roles[0]) || !$this->isAllowed($this->user->roles[0], $resource, "manage")){
			$this->logger->logUnauthorizedAccess();
			throw new HQ\UnauthorizedAccessException;
		}
		return true;
	}

}
