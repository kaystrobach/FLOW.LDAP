<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 30.03.15
 * Time: 11:33
 */

namespace KayStrobach\Ldap\Utility;


class CleanResultUtility {
	/**
	 * @param $array
	 * @return array
	 */
	public static function stripCountFromArray($array) {
		if(array_key_exists('count', $array)) {
			unset($array['count']);
		}
		foreach($array as $key => $value) {
			if((int)$key == $key) {
				unset($array['key']);
				continue;
			}
			if(is_array($value)) {
				$array['key'] = self::stripCountFromArray($value);
			}
		}
		return $array;
	}
}