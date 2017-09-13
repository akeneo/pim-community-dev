<?php

namespace Pim\Component\Catalog\Model;

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
interface ValueCollectionInterface extends ProductUniqueValueCollectionInterface, \Countable, \IteratorAggregate
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
     * @param AttributeInterface $attribute
     *
     * @return bool TRUE if this collection contained values for the specified attribute, FALSE otherwise.
     */
    public function removeByAttribute(AttributeInterface $attribute);

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
     * Get the attributes used in the collection.
     *
     * @return AttributeInterface[]
     */
    public function getAttributes();

    /**
     * Get the attribute codes used in the collection.
     *
     * @return array
     */
    public function getAttributesKeys();

    /**
     * Gets all keys/indices of the collection.
     *
     * @return array The keys/indices of the collection, in the order of the corresponding
     *               values in the collection.
     */
    public function getKeys();

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
}
