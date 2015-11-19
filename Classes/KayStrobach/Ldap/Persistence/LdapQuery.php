<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 05.03.15
 * Time: 07:01
 */

namespace KayStrobach\Ldap\Persistence;

use KayStrobach\Ldap\Service\LdapInterface;
use KayStrobach\Ldap\Utility\EscapeUtility;
use TYPO3\Flow\Persistence\QueryInterface;
use TYPO3\Flow\Annotations as Flow;


class LdapQuery implements QueryInterface, \Countable {
	/**
	 * @var \KayStrobach\Ldap\Service\LdapInterface
	 */
	protected $ldapConnection;

	/**
	 * the query constraint
	 * @var null
	 */
	protected $constraint = NULL;
	/**
	 * @var array in the format array('foo' => QueryInterface::ORDER_ASCENDING, 'bar' => QueryInterface::ORDER_DESCENDING)
	 */
	protected $orderings;
	/**
	 * @var integer
	 */
	protected $limit;
	/**
	 * @var integer
	 */
	protected $offset;

	/**
	 * @var array
	 */
	protected $attributes = NULL;

	/**
	 * @var string
	 * @Flow\Inject(setting="defaults.attributes")
	 */
	protected $defaultAttributes;

	/**
	 * @param LdapInterface $ldapConnection
	 */
	function __construct($ldapConnection) {
		$this->ldapConnection = $ldapConnection;
	}

	/**
	 * Returns the type this query cares for.
	 *
	 * @return string
	 * @api
	 */
	public function getType() {
		return NULL;
	}

	/**
	 * Executes the query and returns the result.
	 *
	 * @param bool $cacheResult If the result cache should be used
	 * @return \TYPO3\Flow\Persistence\QueryResultInterface The query result
	 * @api
	 */
	public function execute($cacheResult = FALSE) {
		return new LdapQueryResult($this);
	}

	/**
	 * Returns the query result count.
	 *
	 * @return integer The query result count
	 * @api
	 */
	public function count() {
		return count($this->getResult()->getAllEntriesAsArray());
	}

	/**
	 * @return \KayStrobach\Ldap\Service\Ldap\Result
	 */
	public function getResult() {
		if($this->getAttributes() === NULL) {
			return $this->ldapConnection->search(NULL, $this->getConstraint());
		} else {
			return $this->ldapConnection->search(NULL, $this->getConstraint(), $this->getAttributes());
		}
	}

	/**
	 * Sets the property names to order the result by. Expected like this:
	 * array(
	 *  'foo' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING,
	 *  'bar' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING
	 * )
	 *
	 * @param array $orderings The property names to order by
	 * @return \TYPO3\Flow\Persistence\QueryInterface
	 * @api
	 */
	public function setOrderings(array $orderings) {
		$this->orderings = $orderings;
	}

	/**
	 * Gets the property names to order the result by, like this:
	 * array(
	 *  'foo' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING,
	 *  'bar' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING
	 * )
	 *
	 * @return array
	 * @api
	 */
	public function getOrderings() {
		return $this->orderings;
	}

	/**
	 * Sets the maximum size of the result set to limit. Returns $this to allow
	 * for chaining (fluid interface).
	 *
	 * @param integer $limit
	 * @return \TYPO3\Flow\Persistence\QueryInterface
	 * @api
	 */
	public function setLimit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Returns the maximum size of the result set to limit.
	 *
	 * @api
	 * @return int
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * Sets the start offset of the result set to offset. Returns $this to
	 * allow for chaining (fluid interface).
	 *
	 * @param integer $offset
	 * @return \TYPO3\Flow\Persistence\QueryInterface
	 * @api
	 */
	public function setOffset($offset) {
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Returns the start offset of the result set.
	 *
	 * @return integer
	 * @api
	 */
	public function getOffset() {
		return $this->offset;
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		if($this->attributes === NULL) {
			return NULL;
		}
		$attributes = array_unique(
			array_merge(
				$this->attributes,
				explode(',', $this->defaultAttributes)
			)
		);
		return $attributes;
	}

	/**
	 * @param array $attributes
	 */
	public function setAttributes($attributes) {
		if (!is_array($attributes)) {
			$attributes = explode(',', $attributes);
		}
		$this->attributes = $attributes;
	}

	/**
	 * The constraint used to limit the result set. Returns $this to allow
	 * for chaining (fluid interface).
	 *
	 * @param object $constraint Some constraint, depending on the backend
	 * @return \TYPO3\Flow\Persistence\QueryInterface
	 * @api
	 */
	public function matching($constraint) {
		$this->constraint = $constraint;
		return $this;
	}

	/**
	 * Gets the constraint for this query.
	 *
	 * @return mixed the constraint, or null if none
	 * @api
	 */
	public function getConstraint() {
		return $this->constraint;
	}

	/**
	 * Performs a logical conjunction of the two given constraints. The method
	 * takes one or more constraints and concatenates them with a boolean AND.
	 * It also accepts a single array of constraints to be concatenated.
	 *
	 * @param mixed $constraint1 The first of multiple constraints or an array of constraints.
	 * @return object
	 * @api
	 */
	public function logicalAnd($constraint1) {
		if(is_array($constraint1)) {
			$constraint1 = implode(' ', $constraint1);
		}
		return '(& ' . $constraint1 . ')';
	}

	/**
	 * Performs a logical disjunction of the two given constraints. The method
	 * takes one or more constraints and concatenates them with a boolean OR.
	 * It also accepts a single array of constraints to be concatenated.
	 *
	 * @param mixed $constraint1 The first of multiple constraints or an array of constraints.
	 * @return object
	 * @api
	 */
	public function logicalOr($constraint1) {
		if(is_array($constraint1)) {
			$constraint1 = implode(' ', $constraint1);
		}
		return '(| ' . $constraint1 . ')';
	}

	/**
	 * Performs a logical negation of the given constraint
	 *
	 * @param object $constraint Constraint to negate
	 * @return object
	 * @api
	 */
	public function logicalNot($constraint) {
		return '(!(' . $constraint . '))';
	}

	/**
	 * Returns an equals criterion used for matching objects against a query.
	 *
	 * It matches if the $operand equals the value of the property named
	 * $propertyName. If $operand is NULL a strict check for NULL is done. For
	 * strings the comparison can be done with or without case-sensitivity.
	 *
	 * @param string $propertyName The name of the property to compare against
	 * @param mixed $operand The value to compare with
	 * @param boolean $caseSensitive Whether the equality test should be done case-sensitive for strings
	 * @return object
	 * @todo Decide what to do about equality on multi-valued properties
	 * @api
	 */
	public function equals($propertyName, $operand, $caseSensitive = TRUE) {
		return $this->contains($propertyName, $operand);
	}

	/**
	 * Returns a like criterion used for matching objects against a query.
	 * Matches if the property named $propertyName is like the $operand, using
	 * standard SQL wildcards.
	 *
	 * @param string $propertyName The name of the property to compare against
	 * @param string $operand The value to compare with
	 * @param boolean $caseSensitive Whether the matching should be done case-sensitive
	 * @return object
	 * @throws \TYPO3\Flow\Persistence\Exception\InvalidQueryException if used on a non-string property
	 * @api
	 */
	public function like($propertyName, $operand, $caseSensitive = TRUE) {
		return '(' . $propertyName . '=*' . EscapeUtility::escape($operand) . '*)';
	}

	/**
	 * Returns a "contains" criterion used for matching objects against a query.
	 * It matches if the multivalued property contains the given operand.
	 *
	 * If NULL is given as $operand, there will never be a match!
	 *
	 * @param string $propertyName The name of the multivalued property to compare against
	 * @param mixed $operand The value to compare with
	 * @return object
	 * @throws \TYPO3\Flow\Persistence\Exception\InvalidQueryException if used on a single-valued property
	 * @api
	 */
	public function contains($propertyName, $operand) {
		return '(' . $propertyName . '=' . EscapeUtility::escape($operand) . ')';
	}

	/**
	 * Returns an "isEmpty" criterion used for matching objects against a query.
	 * It matches if the multivalued property contains no values or is NULL.
	 *
	 * @param string $propertyName The name of the multivalued property to compare against
	 * @return boolean
	 * @throws \TYPO3\Flow\Persistence\Exception\InvalidQueryException if used on a single-valued property
	 * @api
	 */
	public function isEmpty($propertyName) {
		return '(!(' . $propertyName . '=*))';
	}

	/**
	 * Returns an "in" criterion used for matching objects against a query. It
	 * matches if the property's value is contained in the multivalued operand.
	 *
	 * @param string $propertyName The name of the property to compare against
	 * @param mixed $operand The value to compare with, multivalued
	 * @return object
	 * @throws \TYPO3\Flow\Persistence\Exception\InvalidQueryException if used on a multi-valued property
	 * @api
	 */
	public function in($propertyName, $operand) {
		throw new \BadMethodCallException('This method is not implemented in this query implementation.');
	}

	/**
	 * Returns a less than criterion used for matching objects against a query
	 *
	 * @param string $propertyName The name of the property to compare against
	 * @param mixed $operand The value to compare with
	 * @return object
	 * @throws \TYPO3\Flow\Persistence\Exception\InvalidQueryException if used on a multi-valued property or with a non-literal/non-DateTime operand
	 * @api
	 */
	public function lessThan($propertyName, $operand) {
		throw new \BadMethodCallException('This method is not implemented in this query implementation.');
	}

	/**
	 * Returns a less or equal than criterion used for matching objects against a query
	 *
	 * @param string $propertyName The name of the property to compare against
	 * @param mixed $operand The value to compare with
	 * @return object
	 * @throws \TYPO3\Flow\Persistence\Exception\InvalidQueryException if used on a multi-valued property or with a non-literal/non-DateTime operand
	 * @api
	 */
	public function lessThanOrEqual($propertyName, $operand) {
		return '(' . $propertyName . '<=' . EscapeUtility::escape($operand) . ')';
	}

	/**
	 * Returns a greater than criterion used for matching objects against a query
	 *
	 * @param string $propertyName The name of the property to compare against
	 * @param mixed $operand The value to compare with
	 * @return object
	 * @throws \TYPO3\Flow\Persistence\Exception\InvalidQueryException if used on a multi-valued property or with a non-literal/non-DateTime operand
	 * @api
	 */
	public function greaterThan($propertyName, $operand) {
		throw new \BadMethodCallException('This method is not implemented in this query implementation.');
	}

	/**
	 * Returns a greater than or equal criterion used for matching objects against a query
	 *
	 * @param string $propertyName The name of the property to compare against
	 * @param mixed $operand The value to compare with
	 * @return object
	 * @throws \TYPO3\Flow\Persistence\Exception\InvalidQueryException if used on a multi-valued property or with a non-literal/non-DateTime operand
	 * @api
	 */
	public function greaterThanOrEqual($propertyName, $operand) {
		return '(' . $propertyName . '>=' . EscapeUtility::escape($operand) . ')';
	}

	/**
	 * Sets the DISTINCT flag for this query.
	 *
	 * @param boolean $distinct
	 * @return \TYPO3\Flow\Persistence\QueryInterface
	 * @api
	 */
	public function setDistinct($distinct = true)  {
		throw new \BadMethodCallException('This method is not implemented in this query implementation.');
	}

	/**
	 * Returns the DISTINCT flag for this query.
	 *
	 * @return boolean
	 * @api
	 */
	public function isDistinct() {
		throw new \BadMethodCallException('This method is not implemented in this query implementation.');
	}
}