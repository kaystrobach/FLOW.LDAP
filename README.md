KayStrobach.Ldap
================

Settings
--------

Parts of the Settings are inherited from TYPO3.LDAP, all the other stuff has to be configured via Settings.yaml.

```yaml
KayStrobach:
  Ldap:
    host:
    port:
    admin:
      dn:
      password:
```

Usage
-----

If you use TYPO3.Flow you can use the dependency injection, as the factory takes care of initializing the connection.


```php
    /**
     * @var \KayStrobach\Ldap\Service\LdapInterface
     * @Flow\Inject
     */
    protected $ldapConnection;
```

To change a LDAP Passwort, which can really be a hard with pure PHP you can use the following code example:

```php
    $account = $this->ldapConnection->search('dc=example,dc=com', '(uid=' . $this->username . ')');
    if($account->count() === 1) {
        $account->next();
        $this->ldapConnection->bindAsAdmin();
        $account->current()->modify(
            array('userpassword' => KayStrobach\Ldap\Service\Ldap\PasswordUtility::getPasswordArray($newPassword)
            )
        );
        $this->ldapConnection->unbind();
        $this->userSession->setPassword($newPassword);
    } 
    // else ... show some error messages 
```

The Password Utility will then generate all typically needed Password hashes for you (SHA, SSHA, MD4 / NTLM, MD5, SHA256, SHA512) 

Searching in LDAP
-----------------

Simply use $this->ldapConnection->search the method signature is similar to the one from php directly, the difference is,
that all is done object oriented, so that you can ommit the connection and the result resources. 