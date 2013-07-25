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
     * Return configuration
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
}
