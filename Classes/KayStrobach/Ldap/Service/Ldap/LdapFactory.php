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
	 * @param array $settings
	 * @return \KayStrobach\Ldap\Service\LdapInterface
	 */
	public static function create($identifier, $ldapObjectName, $settings) {
		$ldap = new $ldapObjectName();
		$ldap->connect($settings['host'], $settings['port']);
		$ldap->setBaseDn($settings['baseDn']);
		if(!$settings['bind']['anonymous']) {
			$ldap->bind($settings['bind']['dn'], $settings['bind']['password']);
		}
		return $ldap;
	}
}