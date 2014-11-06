<?php

namespace KayStrobach\Ldap\Utility;

/**
 * Class EscapeUtility
 *
 * Used to convert an ldap string, it ensures, that the native function is used im present, if not it uses a fallback
 *
 * Based on http://www.rlmueller.net/CharactersEscaped.htm
 *
 * @package KayStrobach\Ldap\Utility
 */
class EscapeUtility {

	/**
	 * list of chars to escape
	 *
	 * @var string
	 */
	protected static $charsToEscape = ',/\\#+<>;"=';

	/**
	 * function used for escaping
	 *
	 * @api
	 * @param $string
	 * @param string $ignore
	 * @param int $options
	 * @return string
	 */
	public static function escape($string, $ignore = '', $options = 0) {
		if(function_exists('ldap_escape')) {
			return ldap_escape($string, $ignore, $options);
		} else {
			return self::escapeFallback($string, $ignore, $options);
		}
	}

	/**
	 * drop in replacement for ldap_escape
	 *
	 * @todo add LDAP_ESCAPE_DN recognition ...
	 *
	 * @param $string
	 * @param string $ignore
	 * @param int $options
	 * @return string
	 */
	protected static function escapeFallback($string, $ignore = '', $options = NULL) {
		$escapedString = '';
		foreach(str_split($string) as $char) {
			if((strpos(self::$charsToEscape, $char) === FALSE) && (strpos($ignore, $char) === FALSE)) {
				$escapedString .= $char;
			} else {
				$escapedString .= '\\' . $char;
			}
		}

		return $escapedString;
	}
} 