<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 05.03.15
 * Time: 08:33
 */

namespace KayStrobach\Ldap\Domain\Repository;


use KayStrobach\Ldap\Persistence\LdapQuery;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class LdapRepository implements \TYPO3\Flow\Persistence\RepositoryInterface{
	/**
	 * @var \KayStrobach\Ldap\Service\LdapInterface
	 * @Flow\Inject
	 */
	protected $ldapConnection;

	/**
	 * Returns the object type this repository is managing.
	 *
	 * @return string
	 * @api
	 */
	public function getEntityClassName() {
		return NULL;
	}

	/**
	 * Adds an object to this repository.
	 *
	 * @param object $object The object to add
	 * @return void
	 * @api
	 */
	public function add($object) {
		if(is_array($object)) {
			if(array_key_exists('dn', $object)) {
				$this->ldapConnection->add($object['dn'], $object);
			} else {
				throw new \BadMethodCallException('dn key missing in array');
			}
		}
		// @todo add ldap object interface
	}

	/**
	 * Removes an object from this repository.
	 *
	 * @param object $object The object to remove
	 * @return void
	 * @api
	 */
	public function remove($object) {
		if(is_string($object)) {
			$this->ldapConnection->delete($object);
		} elseif(is_array($object)) {
			if(array_key_exists('dn', $object)) {
				$this->ldapConnection->delete($object['dn']);
			} else {
				throw new \BadMethodCallException('dn key missing in array');
			}
		}
		// @todo add ldap object interface
	}

	/**
	 * Returns all objects of this repository.
	 *
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The query result
	 * @api
	 */
	public function findAll() {
		$query = $this->createQuery();
		$query->matching('(cn=*)');
		return $query->execute();
	}

	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param mixed $identifier The identifier of the object to find
	 * @return object The matching object if found, otherwise NULL
	 * @api
	 */
	public function findByIdentifier($identifier) {
		$query = $this->createQuery();
		$query->matching(
			$query->equals('dn', $identifier)
		);
		return $query->execute();
	}

	/**
	 * Returns a query for objects of this repository
	 *
	 * @return \TYPO3\Flow\Persistence\QueryInterface
	 * @api
	 */
	public function createQuery() {
		$this->ldapConnection->bindAsAdmin();
		return new LdapQuery($this->ldapConnection);
	}

	/**
	 * Counts all objects of this repository
	 *
	 * @return integer
	 * @api
	 */
	public function countAll() {
		throw new \BadMethodCallException('Not supported for security reasons');
	}

	/**
	 * Removes all objects of this repository as if remove() was called for
	 * all of them.
	 *
	 * @return void
	 * @api
	 */
	public function removeAll() {
		throw new \BadMethodCallException('Not supported for security reasons');
	}

	/**
	 * Sets the property names to order results by. Expected like this:
	 * array(
	 *  'foo' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING,
	 *  'bar' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING
	 * )
	 *
	 * @param array $defaultOrderings The property names to order by by default
	 * @return void
	 * @api
	 */
	public function setDefaultOrderings(array $defaultOrderings) {
		// TODO: Implement setDefaultOrderings() method.
	}

	/**
	 * Schedules a modified object for persistence.
	 *
	 * @param \KayStrobach\Ldap\Domain\Model\Entry $object The modified object
	 * @return void
	 * @api
	 */
	public function update($object) {
		$this->ldapConnection->bindAsAdmin();
		$this->ldapConnection->modify(
			$object->getDn(),
			$object->getAsArray()
		);
		$this->ldapConnection->unbind();
	}

	/**
	 * Magic call method for repository methods.
	 *
	 * Provides three methods
	 *  - findBy<PropertyName>($value, $caseSensitive = TRUE, $cacheResult = FALSE)
	 *  - findOneBy<PropertyName>($value, $caseSensitive = TRUE, $cacheResult = FALSE)
	 *  - countBy<PropertyName>($value, $caseSensitive = TRUE)
	 *
	 * @param string $method Name of the method
	 * @param array $arguments The arguments
	 * @return mixed The result of the repository method
	 * @api
	 */
	public function __call($method, $arguments) {
		$query = $this->createQuery();
		$caseSensitive = isset($arguments[1]) ? (boolean)$arguments[1] : TRUE;
		$cacheResult = isset($arguments[2]) ? (boolean)$arguments[2] : FALSE;

		if (substr($method, 0, 6) === 'findBy' && strlen($method) > 7) {
			$propertyName = lcfirst(substr($method, 6));
			return $query->matching($query->equals($propertyName, $arguments[0], $caseSensitive))->execute($cacheResult);
		} elseif (substr($method, 0, 7) === 'countBy' && strlen($method) > 8) {
			$propertyName = lcfirst(substr($method, 7));
			return $query->matching($query->equals($propertyName, $arguments[0], $caseSensitive))->count();
		} elseif (substr($method, 0, 9) === 'findOneBy' && strlen($method) > 10) {
			$propertyName = lcfirst(substr($method, 9));
			return $query->matching($query->equals($propertyName, $arguments[0], $caseSensitive))->execute($cacheResult)->getFirst();
		}

		trigger_error('Call to undefined method ' . get_class($this) . '::' . $method, E_USER_ERROR);
	}
}
