<?php

namespace HQ\Model\Entity;

use Nette,
	Nette\Database\Table\ActiveRow;

class BaseEntity extends \Nette\Object
{

	const INCLUDE_DELETED = true;

	/** @var string */
	protected $tableName;

	/** @var Nette\Database\Context */
	private $context;

	public function __construct(
		Nette\Database\Context $context,
		$tableName = null
	) {
		$this->context = $context;

		if (!$this->tableName) {
			$this->tableName = ($tableName ?: $this->getTableNameFromClassName());
		}
	}


	final public function setTableName($tableName)
	{
		$this->tableName = $tableName;
		return $this;
	}

	public function getContext()
	{
		return $this->context;
	}


	public function getTable()
	{
		return $this->context->table($this->tableName);
	}


	public function explodeAndExecute($query) {
		$queryArray = explode(';', $query);

		foreach($queryArray as $queryArrayItem) {
			$queryArrayItem = trim($queryArrayItem);
			if (empty($queryArrayItem)) {
				continue;
			}

			$this->context->prepare($queryArrayItem)->execute();
		}

		return true;
	}


	/**
	 * @param  bool $includeDdeleted
	 * @return \Nette\Database\Table\Selection
	 */
	public function findAll($includeDeleted = false)
	{
		$result = $this->getTable()
			->where($this->tableName . '.' . 'id > 0');

		if (!$includeDeleted) {
			$result->where($this->tableName . '.' . 'del_flag', 0);
		}

		return $result;
	}


	/**
	 * Sets del_flag = 1 for required row
	 * @param  int $id
	 * @return int
	 */
	public function delete($id)
	{
		$row = $this->find($id);

		if (!$row) {
			throw new Nette\InvalidArgumentException("Row with id '$id' does not exist!");
		}

		return $row->update(array(
			"del_flag" => 1,
			"upd_process_id" => __METHOD__,
		));
	}


	/**
	 * @param  array $by
	 * @param  bool  $includeDdeleted
	 * @return \Nette\Database\Table\Selection
	 */
	public function findBy(array $by, $includeDeleted = false)
	{
		return $this->findAll($includeDeleted)->where($by);
	}


	/**
	 * @param  string 		$key
	 * @param  string|NULL 	$value
	 * @param  string|NULL 	$order
	 * @param  bool 		$includeDdeleted
	 * @return array
	 */
	public function fetchPairs($key = "id", $value = "name", $order = "name DESC", $includeDeleted = false)
	{
		$result = $this->findAll($includeDeleted);
		if (!empty($order)) {
			$result->order($order);
		}
		return $result->fetchPairs($key, $value);
	}


	/**
	 * @param array $by
	 * @param  bool $includeDdeleted
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function findOneBy(array $by, $includeDeleted = false)
	{
		return $this->findBy($by, $includeDeleted)->limit(1)->fetch();
	}


	/**
	 * Shortcut for $this->getTable()->insert()
	 * @param  array  $data
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function insert(array $data)
	{
		if (empty($data["ins_dt"])) {
			$data["ins_dt"] = new \DateTime;
		}
		return $this->getTable()->insert($data);
	}

	public function update(ActiveRow $entity, array $data)
	{
		$entity->update($data);
	}


	/**
	 * @param  int $id
	 * @param  bool $includeDdeleted
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function find($id, $includeDeleted = false)
	{
		return $this->findOneBy(array('id' => $id), $includeDeleted);
	}


	/**
	 * Insert row in database or update existing one.
	 * @param  array
	 * @return \Nette\Database\Table\ActiveRow automatically found based on first "column => value" pair in $values
	 */
	public function insertOrUpdate(array $values)
	{
		$this->getContext()->query("INSERT INTO `$this->tableName` ? ON DUPLICATE KEY UPDATE ?", $values, $values);
	}


	public function insertIgnore(array $values)
	{
		$temp_values = $values;
		unset($temp_values["ins_dt"], $temp_values["ins_process_id"], $temp_values["upd_dt"], $temp_values["upd_process_id"]);
		$existingRow = $this->findOneBy($temp_values);
		if($existingRow || $existingRow instanceof \Nette\Database\Table\ActiveRow){
			return $existingRow;
		}
		else{
			try {
				$row = $this->insert($values);
				return $row;
			} catch(\PDOException $e) {
				//$this->logger->logError($e);
			}
		}
	}


	/////////////////////
	// PRIVATE METHODS //
	/////////////////////

	/**
	 * @return string
	 */
	private function getTableNameFromClassName()
	{
		$className = str_replace('Entity', '', get_class($this));
		$tableNameCamelCase = (strrchr($className, "\\")? substr(strrchr($className, "\\"), 1) : $className);
		$splitTableName = preg_split('/(?=[A-Z])/', $tableNameCamelCase, -1, PREG_SPLIT_NO_EMPTY);
		$tableName = strtolower(implode('_', $splitTableName));
		return $tableName;
	}

}
