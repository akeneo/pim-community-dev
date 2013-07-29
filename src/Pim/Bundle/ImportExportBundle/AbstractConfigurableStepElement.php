<?php

namespace Pim\Bundle\ImportExportBundle;

/**
 * Define a configurable step element
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractConfigurableStepElement
{
    /**
     * Return an array of fields for the configuration form
     * @return array:array
     */
    abstract public function getConfigurationFields();

    /**
     * Return name
     * @return string
     */
    abstract public function getName();

    /**
     * Get the step element configuration (based on its properties)
     *
     * @return array
     */
    public function getConfiguration()
    {
        $result = array();
        foreach ($this->getConfigurationFields() as $field => $options) {
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
        foreach ($config as $key => $value) {
            if (!array_key_exists($key, $this->getConfigurationFields())) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown configuration field "%s" in class "%", available fields are "%s"',
                    $field, get_class($this), join('", "', $this->getConfigurationFields())
                ));
            }
            $this->$key = $value;
        }
    }
}
