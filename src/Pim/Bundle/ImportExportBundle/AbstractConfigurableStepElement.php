<?php

namespace Pim\Bundle\ImportExportBundle;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractConfigurableStepElement
{
    abstract public function getConfigurationFields();

    public function getConfiguration()
    {
        $result = array();
        foreach ($this->getConfigurationFields() as $field => $options) {
            $result[$field] = $this->$field;
        }

        return $result;
    }
}
