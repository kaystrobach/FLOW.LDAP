<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.10.14
 * Time: 15:21
 */

namespace KayStrobach\LDAP\Service\Ldap;

/**
 * Class PasswordUtility
 *
 * @package KayStrobach\LDAP\Service\Ldap
 */
class PasswordUtility {
	/**
	 * @param $password
	 * @return array
	 */
	public static function getPasswordArray($password) {
		$passwords = array(
			'{SHA}' . base64_encode(sha1($password, TRUE)),
			'{MD5}' . base64_encode(pack( "H*", md5($password))),
			'{NTLM}' . base64_encode(hash('md4', self::utf8ToUtf16le($password), TRUE)),
		);
		return $passwords;
	}

	/**
	 * @param $string
	 * @return string
	 */
	protected static function utf8ToUtf16le($string) {
		return iconv('UTF-8', 'UTF-16LE', $string);
	}
} 