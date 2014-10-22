<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.10.14
 * Time: 17:52
 */
namespace KayStrobach\LDAP\Service;

use KayStrobach\LDAP\Service\Exception\OperationException;


/**
 * Class Ldap
 *
 * @package KayStrobach\LDAP\Service
 */
interface LdapInterface
{
	/**
	 * @param string $dsn LDAP connection string or hostname
	 * @param string $port LDAP server port
	 * @throws OperationException
	 */
	public function connect($dsn, $port = NULL);

	/**
	 * @return resource
	 */
	public function getResource();

	/**
	 * @return string
	 */
	public function getBaseDn();

	/**
	 * @param string $baseDn
	 */
	public function setBaseDn($baseDn);

	/**
	 * do an ldap bind, if both parameters are empty, anonymous bind is tried
	 *
	 * @param string $rdn
	 * @param string $password
	 * @return bool
	 * @throws OperationException
	 */
	public function bind($rdn = NULL, $password = NULL);

	/**
	 * unbind the ldap connection
	 *
	 * @throws OperationException
	 */
	public function unbind();

	/**
	 * Adds a new Object
	 *
	 * @param string $dn
	 * @param array $entry
	 * @throws OperationException
	 */
	public function add($dn, $entry);

	public function delete($dn);

	/**
	 * http://php.net/manual/de/function.ldap-modify.php
	 *
	 * @param string $dn
	 * @param array $entry
	 */
	public function modify($dn, $entry);

	/**
	 * http://php.net/manual/de/function.ldap-modify-batch.php
	 *
	 * @param $dn
	 * @param $entry
	 * @throws OperationException
	 */
	public function modifyBatch($dn, $entry);

	/**
	 * convert errors to exceptions
	 *
	 * @param $message
	 * @throws OperationException
	 */
	public function checkError($message);

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
	public function search($baseDn = NULL, $filter = '', $attributes = array(), $valuesOnly = NULL, $sizeLimit = NULL, $timeLimit = NULL, $deref = NULL);

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
	public function ls($baseDn, $filter, $attributes = NULL, $valuesOnly = NULL, $sizeLimit = NULL, $timeLimit = NULL, $deref = NULL);

	/**
	 * @param $baseDn
	 * @param $filter
	 */
	public function ensureUnique($baseDn, $filter);

	/**
	 * Queries the LDAP for the highest uid and adds one after wards ...
	 *
	 * @param $dn
	 * @param string $argument
	 * @internal param $ldapConnection
	 * @return int
	 */
	public function getNextUidNumber($dn = NULL, $argument = 'uidNumber');
}