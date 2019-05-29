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
interface IndexedValueCollectionInterface extends ValueCollectionInterface
{
    /**
     * @param ValueCollectionInterface $collection
     *
     * @return ValueCollectionInterface
     */
    public static function fromCollection(ValueCollectionInterface $collection): ValueCollectionInterface;

    /**
     * Adds a value at the end of the collection.
     *
     * @param ValueInterface $value The value to add.
     *
     * @return bool TRUE is the value has been added. FALSE if it was already there.
     */
    public function add(ValueInterface $value);

    /**
     * Clears the collection, removing all values.
     */
    public function clear();

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
     * Removes the value at the specified index from the collection.
     *
     * @param string $key The kex/index of the value to remove.
     *
     * @return ValueInterface|null The removed value or NULL, if the collection did not contain the value.
     */
    public function removeKey($key);

    /**
     * Removes the specified value from the collection, if it is found.
     *
     * @param ValueInterface $value The value to remove.
     *
     * @return bool TRUE if this collection contained the specified value, FALSE otherwise.
     */
    public function remove(ValueInterface $value);

    /**
     * Removes all product values related to a specified attribute (if any).
     *
     * @return bool TRUE if this collection contained values for the specified attribute, FALSE otherwise.
     */
    public function removeByAttributeCode(string $attributeCode);

    /**
     * Checks whether the collection contains a value with the specified key/index.
     *
     * @param string $key The key/index to check for.
     *
     * @return bool TRUE if the collection contains a value with the specified key/index,
     *                 FALSE otherwise.
     */
    public function containsKey($key);

    /**
     * Get the value with the same attribute, channel and locale than $value.
     * Or null if such a value does not exist.
     *
     * @param ValueInterface $value
     *
     * @return ValueInterface|null
     */
    public function getSame(ValueInterface $value);

    /**
     * Gets the value at the specified key/index.
     *
     * @param string $key The key/index of the value to retrieve.
     *
     * @return ValueInterface|null
     */
    public function getByKey($key);

    /**
     * Gets the value at the specified attribute, channel and locale codes.
     *
     * @param string      $attributeCode
     * @param string|null $channelCode
     * @param string|null $localeCode
     *
     * @return ValueInterface|null
     */
    public function getByCodes($attributeCode, $channelCode = null, $localeCode = null);

    /**
     * Gets all keys/indices of the collection.
     *
     * @return array The keys/indices of the collection, in the order of the corresponding
     *               values in the collection.
     */
    public function getKeys();

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
