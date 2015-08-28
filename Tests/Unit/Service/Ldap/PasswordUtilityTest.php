<?php

namespace KayStrobach\Ldap\Tests\Unit\Service\Ldap;

use KayStrobach\Ldap\Service\Ldap\PasswordUtility;

class PasswordUtilityTest extends \TYPO3\Flow\Tests\UnitTestCase {
	public function allHashsReturnedTest() {
		$this->assertEquals(
			count(PasswordUtility::$algorithms),
			count(PasswordUtility::getPasswordArray('test')),
			'There seems to be a problem with detecting some algorithms'
		);
	}
}
