<?php

namespace Akeneo\Bundle\BatchBundle\Item;

/**
 * Object representing a context for an {@link ItemStream}.
 * It also allows for dirty checking by setting a 'dirty' flag whenever any put is called.
 *
 */
class ExecutionContext
{
    /* @var boolean */
    protected $dirty = false;

    /* @var array */
    protected $context = array();

    /**
     * Get the dirty state
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
