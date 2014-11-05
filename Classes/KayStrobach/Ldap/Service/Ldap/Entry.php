<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.10.14
 * Time: 14:57
 */

namespace KayStrobach\Ldap\Service\Ldap;

/**
 * Class LdapEntry
 *
 * @package KayStrobach\Ldap\Service
 */
class Entry {
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
		$dn = ldap_get_dn($this->ldapConnection->getResource(), $this->entryAsResource);
		$this->ldapConnection->checkError('getDn');
		return $dn;
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


} 