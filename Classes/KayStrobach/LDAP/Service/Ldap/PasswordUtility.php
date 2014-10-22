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
	 * creates an array with preencrypted password inside to easily use the modify function without hassling with
	 * the f*** encryption stuff
	 *
	 * @param $password
	 * @return array
	 */
	public static function getPasswordArray($password) {
		$passwords = array(
			'{MD5}' . base64_encode(pack( "H*", md5($password))),
			'{NTLM}' . base64_encode(hash('md4', self::utf8ToUtf16le($password), TRUE)),
			'{SHA}' . base64_encode(sha1($password, TRUE)),
			'{SHA256}' . base64_encode(hash('sha256', $password, TRUE)),
			'{SHA512}' . base64_encode(hash('sha512', $password, TRUE)),
		);
		return $passwords;
	}

	/**
	 * convert charset to match windows patterns
	 *
	 * @param $string
	 * @return string
	 */
	protected static function utf8ToUtf16le($string) {
		return iconv('UTF-8', 'UTF-16LE', $string);
	}
} 