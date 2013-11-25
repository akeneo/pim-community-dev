<?php

namespace Pim\Bundle\CustomEntityBundle\Configuration;

/**
 * Registry of configurations
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Registry
{
    /**
     * @var array
     */
    protected $configurations = array();

    /**
     * Returns true if a configuration with the corresponding name exists
     *
     * @param string $name
     *
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->configurations[$name]);
    }

    /**
     * Get a configuration
     *
     * @param string $name
     *
     * @return ConfigurationInterface
     */
    public function get($name)
    {
        return $this->configurations[$name];
    }

    /**
     * Add a configuration
     *
     * @param ConfigurationInterface $configuration
     */
    public function add(ConfigurationInterface $configuration)
    {
        $this->configurations[$configuration->getName()] = $configuration;
    }
}
