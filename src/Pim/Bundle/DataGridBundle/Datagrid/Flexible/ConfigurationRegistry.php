<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

/**
 * This registry contains the grid configuration for each attribute type (cf grid_attribute_types.yml) and helps
 * to configure grid column, filter and sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationRegistry
{
    /**
     * @param array
     */
    protected $configurations;

    /**
     * Instanciate the registry
     */
    public function __construct()
    {
        $this->configurations = array();
    }

    /**
     * @param array $configurations
     */
    public function setConfigurations($configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * Check if registry has a configuration for this attribute type
     *
     * @param string $attributeType the type
     *
     * @return boolean
     */
    public function hasConfiguration($attributeType)
    {
        return isset($this->configurations[$attributeType]);
    }

    /**
     * Get the configuration related to this attribute type
     *
     * @param string $attributeType the type
     *
     * @return array
     */
    public function getConfiguration($attributeType)
    {
        if ($this->hasConfiguration($attributeType)) {
            return $this->configurations[$attributeType];
        }

        return null;
    }
}
