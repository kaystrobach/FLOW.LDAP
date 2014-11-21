<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.10.14
 * Time: 15:21
 */

namespace KayStrobach\Ldap\Service\Ldap;

/**
 * Class PasswordUtility
 *
 * @package KayStrobach\Ldap\Service\Ldap
 */
class PasswordUtility {
	/**
	 * creates an array with preencrypted password inside to easily use the modify function without hassling with
	 * the f*** encryption stuff
	 *
	 * following http://tools.ietf.org/id/draft-stroeder-hashed-userpassword-values-01.html#rfc.section.2
	 *
	 * prefix     Description          Algorithm reference
	 * {MD4}      MD-5 without salt    [RFC1321]
	 * {MD5}      MD-5 without salt    [RFC1321]
	 * {SMD5}     salted MD-5          [RFC1321]
	 * {SHA}      SHA-1 without salt   [FIPS-180-4]
	 * {SSHA}     salted SHA-1         [FIPS-180-4]
	 * {SHA256}   SHA-256 without salt [FIPS-180-4]
	 * {SSHA256}  salted SHA-256       [FIPS-180-4]
	 * {SHA384}   SHA-384 without salt [FIPS-180-4]
	 * {SSHA384}  salted SHA-384       [FIPS-180-4]
	 * {SHA512}   SHA-512 without salt [FIPS-180-4]
	 * {SSHA512}  salted SHA-512       [FIPS-180-4]
	 *
	 * @param $password
	 * @return array
	 */
	public static function getPasswordArray($password) {
		$passwords = array(
			'{MD4}' . base64_encode(pack( "H*", hash('md4', $password))),
			'{MD5}' . base64_encode(pack( "H*", md5($password))),
			'{SMD5}' . self::saltedHash('md5', $password),
			'{NTLM}' . base64_encode(hash('md4', self::utf8ToUtf16le($password), TRUE)),
			'{SHA}' . base64_encode(sha1($password, TRUE)),
			'{SSHA}' . self::ssha($password),
			'{SHA256}' . base64_encode(hash('sha256', $password, TRUE)),
			'{SSHA256}' . self::saltedHash('sha256', $password),
			'{SHA384}' . base64_encode(hash('sha384', $password, TRUE)),
			'{SSHA384}' . self::saltedHash('sha384', $password),
			'{SHA512}' . base64_encode(hash('sha512', $password, TRUE)),
			'{SSHA512}' . self::saltedHash('sha512', $password),
		);
		// add some blowfish passwords if needed
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

	protected static function saltedHash($algorithm, $password) {
		$salt = self::generateSalt(10);
		return base64_encode(pack("H*", hash($algorithm, $password . $salt)) . $salt);
	}

	protected static function ssha($string) {
		$salt = self::generateSalt(10);
		return base64_encode(pack("H*", sha1($string . $salt)) . $salt);
	}

	protected static function generateSalt($length = 10) {
		$salt = "";
		for ($i = 1; $i <= $length; $i++) {
			$salt .= substr('0123456789abcdefghijklmnopqrstuvwxyz', rand(0, 36), 1);
		}
	}
} 