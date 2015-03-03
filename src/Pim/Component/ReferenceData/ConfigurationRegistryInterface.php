<?php

namespace Pim\Component\ReferenceData;

use Pim\Component\ReferenceData\Model\ConfigurationInterface;

interface ConfigurationRegistryInterface
{
    /**
     * @param ConfigurationInterface $configuration
     * @param string                 $name
     *
     * @return ConfigurationRegistryInterface
     */
    public function register(ConfigurationInterface $configuration, $name);

    /**
     * @param array  $configuration
     * @param string $name
     *
     * @return ConfigurationRegistryInterface
     */
    public function registerRaw(array $configuration, $name);

    /**
     * @param string $name
     *
     * @return ConfigurationInterface
     */
    public function get($name);

    /**
     * @return ConfigurationInterface[]
     */
    public function all();

    /**
     * @param string $name
     *
     * @return ConfigurationRegistryInterface
     */
    public function unregister($name);
}
