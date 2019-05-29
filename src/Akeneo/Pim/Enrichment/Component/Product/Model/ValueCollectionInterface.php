<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Business collection interface to handle product values.
 * It does not extends \ArrayAccess as indexation could be performed internally
 * for performance reasons.
 *
 * Highly inspired by \Doctrine\Common\Collection.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ValueCollectionInterface extends \Countable, \IteratorAggregate
{
    /**
     * Get the attributes used in the collection.
     *
     * @return string[]
     */
    public function getAttributeCodes();

    /**
     * Checks whether a value is contained in the collection.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param ValueInterface $value The value to search for.
     *
     * @return bool TRUE if the collection contains the value, FALSE otherwise.
     */
    public function contains(ValueInterface $value);

    /**
     * Checks whether the collection is empty (contains no values).
     *
     * @return bool TRUE if the collection is empty, FALSE otherwise.
     */
    public function isEmpty();

    /**
     * Gets all values of the collection.
     *
     * @return array The values of all values in the collection, in the order they
     *               appear in the collection.
     */
    public function getValues();

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array
     */
    public function toArray();

    /**
     * Sets the internal iterator to the first value in the collection and returns this value.
     *
     * @return ValueInterface
     */
    public function first();

    /**
     * Sets the internal iterator to the last value in the collection and returns this value.
     *
     * @return ValueInterface
     */
    public function last();

    /**
     * Gets the key/index of the value at the current iterator position.
     *
     * @return string
     */
    public function key();

    /**
     * Gets the value of the collection at the current iterator position.
     *
     * @return ValueInterface
     */
    public function current();

    /**
     * Moves the internal iterator position to the next value and returns this value.
     *
     * @return ValueInterface
     */
    public function next();

    /**
     * Returns all the elements of this collection that satisfy the predicate $closure.
     * The order of the elements is preserved.
     * A **new** collection is returned.
     *
     * @param \Closure $filterBy
     *
     * @return ValueCollectionInterface
     */
    public function filter(\Closure $filterBy);
}
