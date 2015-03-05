<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 05.03.15
 * Time: 07:36
 */

namespace KayStrobach\Ldap\Persistence;

use TYPO3\Flow\Persistence\QueryResultInterface;
use TYPO3\Flow\Annotations as Flow;

class LdapQueryResult implements QueryResultInterface {
	/**
	 * @var \KayStrobach\Ldap\Service\LdapInterface
	 * @Flow\Inject
	 */
	protected $ldapConnection;

	/**
	 * @var LdapQuery
	 */
	protected $query;
	/**
	 * @var \KayStrobach\Ldap\Service\Ldap\Result
	 */
	protected $resultObject;

	/**
	 * @param LdapQuery $query
	 */
	public function __construct(LdapQuery $query) {
		$this->query = $query;
	}

	/**
	 * Loads the objects this QueryResult is supposed to hold
	 *
	 * @return void
	 */
	protected function initialize() {
		if ($this->resultObject === NULL) {
			$this->resultObject = $this->query->getResult();
		}
	}
	/**
	 * Returns a clone of the query object
	 *
	 * @return LdapQuery
	 */
	public function getQuery() {
		return clone $this->query;
	}

	/**
	 * Returns the first object in the result set
	 *
	 * @return object
	 */
	public function getFirst() {
		$this->initialize();
		$this->resultObject->rewind();
		return $this->resultObject->current();
	}

	/**
	 * Returns the number of objects in the result
	 *
	 * @return integer The number of matching objects
	 */
	public function count() {
		$this->initialize();
		return $this->resultObject->count();
	}

	/**
	 * Returns an array with the objects in the result set
	 *
	 * @return array
	 */
	public function toArray() {
		$this->initialize();
		return $this->resultObject->getAllEntriesAsArray();
	}

	/**
	 * This method is needed to implement the \ArrayAccess interface,
	 * but it isn't very useful as the offset has to be an integer
	 *
	 * @param mixed $offset
	 * @return boolean
	 */
	public function offsetExists($offset) {
		$this->initialize();
		if($this->resultObject->count() < $offset) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	/**
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		$this->initialize();
		return isset($this->resultObject[$offset]) ? $this->resultObject[$offset] : NULL;
	}
	/**
	 * This method has no effect on the persisted objects but only on the result set
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->initialize();
		$this->resultObject[$offset] = $value;
	}
	/**
	 * This method has no effect on the persisted objects but only on the result set
	 *
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->initialize();
		unset($this->resultObject[$offset]);
	}
	/**
	 * @return mixed
	 */
	public function current() {
		$this->initialize();
		return $this->resultObject->current();
	}
	/**
	 * @return mixed
	 */
	public function key() {
		$this->initialize();
		return $this->resultObject->key();
	}
	/**
	 * @return void
	 */
	public function next() {
		$this->initialize();
		$this->resultObject->next();
	}
	/**
	 * @return void
	 */
	public function rewind() {
		$this->initialize();
		$this->resultObject->rewind();
	}
	/**
	 * @return boolean
	 */
	public function valid() {
		$this->initialize();
		return $this->resultObject->valid();
	}
}