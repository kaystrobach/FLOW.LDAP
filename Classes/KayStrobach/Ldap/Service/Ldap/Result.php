<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 21.10.14
 * Time: 16:30
 */

namespace KayStrobach\Ldap\Service\Ldap;
use KayStrobach\Ldap\Service\Exception\OperationException;

/**
 * Class LdapResult
 *
 * has all the ldap handling build in
 *
 * @package KayStrobach\Ldap\Service
 */
class Result implements \Iterator{
	/**
	 * pointer to the ldap connection
	 *
	 * @var \KayStrobach\Ldap\Service\Ldap
	 */
	protected $ldapConnection;

	/**
	 * pointer to the ldap result
	 *
	 * @var resource
	 */
	protected $ldapResult;

	/**
	 * @var int
	 */
	protected $position = 0;

	/**
	 * @var null
	 */
	protected $entryAsResource = FALSE;

	/**
	 * @param Ldap $ldapConnection
	 * @param resource $ldapResult
	 */
	public function __construct($ldapConnection, $ldapResult) {
		$this->ldapConnection= $ldapConnection;
		$this->ldapResult = $ldapResult;
	}

	/**
	 * @return int
	 */
	public function count() {
		$count = ldap_count_entries($this->ldapConnection->getResource(), $this->ldapResult);
		$this->ldapConnection->checkError('count');
		return $count;
	}

	/**
	 * @param $field
	 * @throws OperationException
	 */
	public function sort($field) {
		if($this->position !== 0) {
			ldap_sort($this->ldapConnection->getResource(), $this->ldapResult, $field);
			$this->ldapConnection->checkError('sort');
		} else {
			throw new OperationException('Sort must be called before iterating through the result!');
		}
	}

	/**
	 * @return array
	 */
	public function getAllEntriesAsArray() {
		$entries = ldap_get_entries($this->ldapConnection->getResource(), $this->ldapResult);
		$this->ldapConnection->checkError('getAllEntries');
		// remove the index 0, as it just contains the count
		array_shift($entries);
		return $entries;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return Entry Can return any type.
	 */
	public function current() {
		if($this->entryAsResource !== NULL) {
			return new Entry($this->ldapConnection, $this->entryAsResource);
		}
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		if($this->position === 0) {
			$this->entryAsResource = ldap_first_entry($this->ldapConnection->getResource(), $this->ldapResult);
			$this->ldapConnection->checkError('firstEntry');
		} else {
			$this->entryAsResource = ldap_next_entry($this->ldapConnection->getResource(), $this->ldapResult);
			$this->ldapConnection->checkError('nextEntry');
		}
		if($this->valid()) {
			$this->position++;
		}
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		if($this->entryAsResource !== FALSE) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		$this->position = 0;
		$this->entryAsResource = FALSE;
	}
}