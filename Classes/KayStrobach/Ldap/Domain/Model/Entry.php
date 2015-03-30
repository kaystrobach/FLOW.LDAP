<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.10.14
 * Time: 14:57
 */

namespace KayStrobach\Ldap\Domain\Model;
use KayStrobach\Ldap\Service\Exception\OperationException;

/**
 * Class LdapEntry
 *
 * @package KayStrobach\Ldap\Service
 */
class Entry implements \ArrayAccess {
	/**
	 * pointer to the ldap connection
	 *
	 * @var \KayStrobach\Ldap\Service\Ldap
	 */
	protected $ldapConnection;

	/**
	 * @var null
	 */
	protected $entryAsArray = NULL;

	/**
	 * @var null
	 */
	protected $entryAsResource = FALSE;

	/**
	 * @param \KayStrobach\Ldap\Service\Ldap $ldapConnection
	 * @param resource $ldapResult
	 * @param array $entryArray
	 */
	public function __construct($ldapConnection, $ldapResult, $entryArray = NULL) {
		$this->ldapConnection= $ldapConnection;
		$this->entryAsResource = $ldapResult;
		if($entryArray !== NULL) {
			$this->entryAsArray = $entryArray;
		}
	}

	/**
	 * @return array|null
	 * @throws \KayStrobach\Ldap\Service\Exception\OperationException
	 */
	public function getAsArray() {
		if($this->entryAsArray === NULL) {
			$this->entryAsArray = ldap_get_attributes($this->ldapConnection->getResource(), $this->entryAsResource);
			$this->ldapConnection->checkError('getAttributes');
		}
		return $this->entryAsArray;
	}

	/**
	 * @return string
	 * @throws \KayStrobach\Ldap\Service\Exception\OperationException
	 */
	public function getDn() {
		if(is_resource($this->entryAsResource)) {
			$dn = ldap_get_dn($this->ldapConnection->getResource(), $this->entryAsResource);
			$this->ldapConnection->checkError('getDn');
			return $dn;
		} elseif(array_key_exists('dn', $this->getAsArray())) {
			return $this->getAsArray()['dn'];
		} else {
			throw new OperationException('Can´t detect DN of object');
		}
	}

	/**
	 * @param string $name
	 * @return array
	 * @throws \KayStrobach\Ldap\Service\Exception\OperationException
	 */
	public function getValues($name = NULL) {
		if($name !== NULL) {
			$values = ldap_get_values($this->ldapConnection->getResource(), $this->entryAsResource, $name);
			$this->ldapConnection->checkError('getValues' . $name);
			return $values;
		}
		return NULL;
	}

	/**
	 * @param array $data
	 * @return bool
	 * @throws \KayStrobach\Ldap\Service\Exception\OperationException
	 */
	public function modify($data) {
		$dn = $this->getDn();
		$state = ldap_modify($this->ldapConnection->getResource(), $dn, $data);
		$this->ldapConnection->checkError('modify' . print_r($data, TRUE));
		return $state;
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->entryAsArray[] = $value;
		} else {
			$this->entryAsArray[$offset] = $value;
		}
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return isset($this->entryAsArray[$offset]);
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset) {
		unset($this->entryAsArray[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @return null
	 */
	public function offsetGet($offset) {
		return isset($this->entryAsArray[$offset]) ? $this->entryAsArray[$offset] : null;
	}
} 