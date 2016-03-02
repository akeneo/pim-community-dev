<?php

namespace Akeneo\Component\Batch\Item;

use Doctrine\Common\Util\Inflector;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Define a configurable step element
 *
 * @abstract
 */
abstract class AbstractConfigurableStepElement
{
    /**
     * Return an array of fields for the configuration form
     *
     * @return array:array
     *
     * @abstract
     */
    abstract public function getConfigurationFields();

    /**
     * Return name
     *
     * @return string
     */
    public function getName()
    {
        $classname = get_class($this);

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return Inflector::tableize($classname);
    }

    /**
     * Get the step element configuration (based on its properties)
     *
     * @return array
     */
    public function getConfiguration()
    {
        $result = array();
        foreach (array_keys($this->getConfigurationFields()) as $field) {
            $result[$field] = $this->$field;
        }

        return $result;
    }

    /**
     * Set the step element configuration
     *
     * @param array $config
     */
    public function setConfiguration(array $config)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($config as $key => $value) {
            if (array_key_exists($key, $this->getConfigurationFields())) {
                $accessor->setValue($this, $key, $value);
            }
        }
    }

    /**
     * Override to add custom logic on step initialization.
     */
    public function initialize()
    {
    }

    /**
     * Override to add custom logic on step completion.
     */
    public function flush()
    {
    }
}
