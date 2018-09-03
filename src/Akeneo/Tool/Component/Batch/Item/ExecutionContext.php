<?php

namespace Akeneo\Tool\Component\Batch\Item;

/**
 * Object representing a context for an {@link ItemStream}.
 * It also allows for dirty checking by setting a 'dirty' flag whenever any put is called.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ExecutionContext
{
    /* @var boolean */
    protected $dirty = false;

    /* @var array */
    protected $context = [];

    /**
     * Get the dirty state
     *
     * @return boolean
     */
    public function isDirty()
    {
        return $this->dirty;
    }

    /**
     * Clear the dirty flag
     *
     * @return $this
     */
    public function clearDirtyFlag()
    {
        $this->dirty = false;

        return $this;
    }

    /**
     * Get the value associated with the key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        $value = null;

        if (isset($this->context[$key])) {
            $value = $this->context[$key];
        }

        return $value;
    }

    /**
     * Put a key-value pair in the context
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function put($key, $value)
    {
        $this->context[$key] = $value;

        $this->dirty = true;

        return $this;
    }

    /**
     * Remove a key-value pair from the context
     * by using the key
     *
     * @param string $key
     *
     * @return $this
     */
    public function remove($key)
    {
        if (isset($this->context[$key])) {
            unset($this->context[$key]);
        }

        return $this;
    }

    /**
     * Provides the list of keys available in the context
     *
     * @return array $keys
     */
    public function getKeys()
    {
        return array_keys($this->context);
    }
}
