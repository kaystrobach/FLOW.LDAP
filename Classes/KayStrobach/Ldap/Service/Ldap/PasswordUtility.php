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
	public static $algorithms = array(
		'MD4',
		'MD5',
		'SMD5',
		'NTLM',
		'SHA',
		'SSHA',
		'SHA256',
		'SSHA256',
		'SHA384',
		'SSHA384',
		'SHA512',
		'SSHA512'
	);
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
		$passwords = array();
		foreach(self::$algorithms as $algorithm) {
			$hashFunctionName = 'hash' . strtolower(ucfirst($algorithm));
			if(method_exists(__CLASS__, $hashFunctionName)) {
				 $hashedPassword = call_user_func(
					array(__CLASS__, $hashFunctionName),
					$password
				);
				$passwords[] = '{' . $algorithm . '}' . base64_encode($hashedPassword);
			}
		}
		return $passwords;
	}

	public static function hashMd4($password) {
		return pack( "H*", hash('md4', $password));
	}

	public static function hashMd5($password) {
		return pack( "H*", md5($password));
	}

	public static function hashSmd5($password) {
		return self::saltedHash('md5', $password);
	}
	public static function hashNtlm($password) {
		return self::hashMd4(self::utf8ToUtf16le($password));
	}

	public static function hashSha($password) {
		return sha1($password, TRUE);
	}

	public static function hashSsha($password) {
		self::ssha($password);
	}

	public static function hashSha256($password) {
		return hash('sha256', $password, TRUE);
	}

	public static function hashSsha256($password) {
		return self::saltedHash('sha256', $password);
	}

	public static function hashSha384($password) {
		return hash('sha384', $password, TRUE);
	}

	public static function hashSsha384($password) {
		return self::saltedHash('sha384', $password);
	}

	public static function hashSha512($password) {
		return hash('sha512', $password, TRUE);
	}

	public static function hashSsha512($password) {
		return self::saltedHash('sha512', $password);
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

	protected static function saltedHash($algorithm, $password, $salt = NULL) {
		if($salt === NULL) {
			$salt = self::generateSalt(10);
		}
		return base64_encode(pack("H*", hash($algorithm, $password . $salt)) . $salt);
	}

	protected static function ssha($string, $salt = NULL) {
		if($salt === NULL) {
			$salt = self::generateSalt(10);
		}
		return base64_encode(pack("H*", sha1($string . $salt)) . $salt);
	}

	protected static function generateSalt($length = 10) {
		$salt = "";
		for ($i = 1; $i <= $length; $i++) {
			$salt .= substr('0123456789abcdefghijklmnopqrstuvwxyz', rand(0, 36), 1);
		}
	}
} 