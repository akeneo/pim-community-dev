<?php

namespace Pim\Bundle\ImportExportBundle;

use Doctrine\Common\Util\Inflector;

/**
 * Define a configurable step element
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        foreach ($config as $key => $value) {
            if (array_key_exists($key, $this->getConfigurationFields())) {
                $this->$key = $value;
            }
        }
    }
}
