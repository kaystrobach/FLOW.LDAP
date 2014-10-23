<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.10.14
 * Time: 15:51
 */

namespace KayStrobach\Ldap\Service\Ldap;

use KayStrobach\Ldap\Service\Ldap;
use TYPO3\Flow\Annotations as Flow;

/**
 * Class Factory
 * @package KayStrobach\Ldap\Service\Ldap
 *
 * @Flow\Scope("singleton")
 */
class LdapFactory {
	/**
	 * @param string $identifier
	 * @param string $ldapObjectName
	 * @param array $authSettings
	 * @return \KayStrobach\Ldap\Service\LdapInterface
	 */
	public static function create($identifier, $ldapObjectName, $authSettings, $settings) {
		/** @var \KayStrobach\Ldap\Service\LdapInterface $ldap */
		$ldap = new $ldapObjectName();
		$ldap->connect($authSettings['host'], $authSettings['port']);
		$ldap->setBaseDn($authSettings['baseDn']);
		$ldap->configure($settings);
		if(!$authSettings['bind']['anonymous']) {
			$ldap->bind($authSettings['bind']['dn'], $authSettings['bind']['password']);
		}
		return $ldap;
	}
}