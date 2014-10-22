<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 07.10.14
 * Time: 12:17
 */

namespace KayStrobach\LDAP\Service;


use KayStrobach\LDAP\Service\Exception\OperationException;
use TYPO3\Flow\Annotations as Flow;

/**
 * Class Ldap
 *
 * @package KayStrobach\LDAP\Service
 */
class Ldap implements LdapInterface {
	/**
	 * pointer to the ldap connection
	 *
	 * @var resource
	 */
	protected $ldapResource;

	/**
	 * Saves the ldap bind status
	 *
	 * @var bool
	 */
	protected $ldapBindStatus = FALSE;

	/**
	 * BaseDn used in some functions
	 *
	 * @var string
	 */
	protected $baseDn = NULL;

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $systemLogger;

	/**
	 * close connection before destroying the object
	 */
	public function __destruct() {
		$this->unbind();
	}

	/**
	 * @param string $dsn LDAP connection string or hostname
	 * @param string $port LDAP server port
	 * @throws OperationException
	 */
	public function connect($dsn, $port = NULL) {
		if($port !== NULL) {
			$this->ldapResource = ldap_connect($dsn, $port);
		} else {
			$this->ldapResource = ldap_connect($dsn);
		}
		if($this->ldapResource === FALSE) {
			throw new OperationException('LDAP Connection failed');
		}
		$this->checkError('connect');
		ldap_set_option($this->ldapResource, LDAP_OPT_PROTOCOL_VERSION, 3);
		$this->checkError('protocol 3');
	}

	/**
	 * @param boolean $alsoCheckBind
	 * @throws OperationException
	 */
	protected function checkConnection($alsoCheckBind = FALSE) {
		if(!$this->ldapResource) {
			throw new OperationException('ldap does not seem to be connected');
		}
		if($alsoCheckBind && !$this->ldapBindStatus) {
			throw new OperationException('ldap bind is missing');
		}
	}

	/**
	 * @return resource
	 */
	public function getResource() {
		return $this->ldapResource;
	}

	/**
	 * @return string
	 */
	public function getBaseDn() {
		return $this->baseDn;
	}

	/**
	 * @param string $baseDn
	 */
	public function setBaseDn($baseDn) {
		$this->baseDn = $baseDn;
	}

	/**
	 * do an ldap bind, if both parameters are empty, anonymous bind is tried
	 *
	 * @param string $rdn
	 * @param string $password
	 * @return bool
	 * @throws OperationException
	 */
	public function bind($rdn = NULL, $password = NULL) {
		$this->checkConnection();
		if(($rdn !== NULL) && ($password !== NULL)) {
			$this->ldapBindStatus = ldap_bind($this->ldapResource, $rdn, $password);
		} else {
			$this->ldapBindStatus = ldap_bind($this->ldapResource);
		}
		$this->checkError('bind');
		return $this->ldapBindStatus;
	}

	/**
	 * unbind the ldap connection
	 *
	 * @throws OperationException
	 */
	public function unbind() {
		if($this->ldapResource) {
			ldap_unbind($this->ldapResource);
			$this->ldapBindStatus = FALSE;
			$this->checkError('unbind');
		}
	}

	/**
	 * Adds a new Object
	 *
	 * @param string $dn
	 * @param array $entry
	 * @throws OperationException
	 */
	public function add($dn, $entry) {
		$this->checkConnection();
		ldap_add($this->ldapResource, $dn, $entry);
		$this->checkError('add ' . $dn);
	}

	public function delete($dn) {
		ldap_delete($this->ldapResource, $dn);
		$this->checkError('delete ' . $dn);
	}

	/**
	 * http://php.net/manual/de/function.ldap-modify.php
	 *
	 * @param string $dn
	 * @param array $entry
	 */
	public function modify($dn, $entry) {
		$this->checkConnection();
		ldap_modify($this->ldapResource, $dn, $entry);
		$this->checkError('modify ' . $dn);
	}

	/**
	 * http://php.net/manual/de/function.ldap-modify-batch.php
	 *
	 * @param $dn
	 * @param $entry
	 * @throws OperationException
	 */
	public function modifyBatch($dn, $entry) {
		$this->checkConnection();
		if(function_exists('ldap_modify_batch')) {
			ldap_modify_batch($this->ldapResource, $dn, $entry);
			$this->checkError('modify batch ' . $dn);
		} else {
			throw new OperationException('function ldap_modify_batch is missing');
		}
	}

	/**
	 * convert errors to exceptions
	 *
	 * @param $message
	 * @throws OperationException
	 */
	public function checkError($message) {
		$ldapError = ldap_errno($this->ldapResource);
		if($ldapError !== 0x00) {
			$exceptionMessage = 'LDAP error: ' . $message . ': ' . ldap_err2str($ldapError);
			throw new OperationException($exceptionMessage, $ldapError);
		}
		$this->systemLogger->log('LDAP-OK: ' . $message, LOG_DEBUG);
	}

	/**
	 * subtree search
	 *
	 * @param string $baseDn
	 * @param string $filter
	 * @param array $attributes
	 * @param int $valuesOnly
	 * @param int $sizeLimit
	 * @param int $timeLimit
	 * @param int $deref
	 * @return \KayStrobach\LDAP\Service\Ldap\Result
	 */
	public function search($baseDn = NULL, $filter = '', $attributes = array(), $valuesOnly = NULL, $sizeLimit = NULL, $timeLimit = NULL, $deref = NULL) {
		$this->checkConnection();
		if(($baseDn === NULL) && ($this->baseDn !== NULL)) {
			$baseDn = $this->baseDn;
		}
		$result = ldap_search($this->ldapResource, $baseDn, $filter, $attributes, $valuesOnly, $sizeLimit, $timeLimit, $deref);
		$this->checkError('seach');
		return new Ldap\Result($this, $result);
	}

	/**
	 * one level search
	 *
	 * @param string $baseDn
	 * @param string $filter
	 * @param array $attributes
	 * @param int $valuesOnly
	 * @param int $sizeLimit
	 * @param int $timeLimit
	 * @param int $deref
	 * @return \KayStrobach\LDAP\Service\Ldap\Result
	 */
	public function ls($baseDn, $filter, $attributes = NULL, $valuesOnly = NULL, $sizeLimit = NULL, $timeLimit = NULL, $deref = NULL) {
		$this->checkConnection();
		if(($baseDn === NULL) && ($this->baseDn !== NULL)) {
			$baseDn = $this->baseDn;
		}
		$result = ldap_list($this->ldapResource, $baseDn, $filter, $attributes, $valuesOnly, $sizeLimit, $timeLimit, $deref);
		$this->checkError('ls');
		return new Ldap\Result($this, $result);
	}

	/**
	 * @param $baseDn
	 * @param $filter
	 */
	public function ensureUnique($baseDn, $filter) {
		$this->checkConnection();
		$this->search($baseDn, $filter);
	}

	/**
	 * Queries the LDAP for the highest uid and adds one after wards ...
	 *
	 * @param $dn
	 * @param string $argument
	 * @internal param $ldapConnection
	 * @return int
	 */
	public function getNextUidNumber($dn = NULL, $argument = 'uidNumber') {
		$erg = $this->search($dn, $argument . '=*', array($argument));
		$entries = ldap_get_entries($this->ldapResource, $erg);
		$entry = 0;
		foreach($entries as $currentEntry) {
			if(array_key_exists('uidnumber', $currentEntry)) {
				if((int)$currentEntry['uidnumber'][0] > $entry) {
					$entry = (int)$currentEntry['uidnumber'][0];
				}
			}
		}
		return $entry + 1;
	}
} 