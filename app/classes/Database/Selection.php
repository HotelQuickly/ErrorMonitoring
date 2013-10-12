<?php

namespace HQ\Database\Table;

/**
* Extension of \Nette\Database\Table\Selection
* @author Josef Nevoral <josef.nevoral@gmail.com>
*/
class Selection extends \Nette\Database\Table\Selection
{
	/**
	 * User object
	 * @var \Nette\Security\User
	 */
	private $user;

	/**
	 * Overrides default constructor and adds $user
	 * @param string                    $table
	 * @param Nette\Database\Connection $connection
	 * @param int                    	$user     user object
	 */
	public function __construct($table, \Nette\Database\Connection $connection, $user)
	{
		parent::__construct($table, $connection);

		$this->user = $user;
	}

	/**
	 * Overrides default insert method to add who inserted to each table
	 * @param  array|Traversable $data
	 * @return ActiveRow or FALSE in case of an error or number of affected rows for INSERT ... SELECT
	 */
	public function insert($data)
	{
		if ($data instanceof \Traversable) {
			$data->ins_user_id = $this->_getUserId();
		} else if (is_array($data)) {
			$data['ins_user_id'] = $this->_getUserId();
		}

		return parent::insert($data);
	}

	/**
	 * Overrides default update method for adding information who updated to each table
	 * @param   array|\Traversable ($column => $value)
	 * @return int number of affected rows or FALSE in case of an error
	 */
	public function update($data)
	{
		if ($data instanceof \Traversable) {
			$data->ins_user_id = $this->_getUserId();
		} else if (is_array($data)) {
			$data['upd_user_id'] = $this->_getUserId();
		}

		return parent::update($data);
	}

	/**
	 * Get actual logged in user_id
	 * @return int aktual logged in user_id or -1 for not logged in users
	 */
	private function _getUserId()
	{
		if ($this->user && $this->user->isLoggedIn()) {
			return $this->user->id;
		}
		return -1;
	}
}