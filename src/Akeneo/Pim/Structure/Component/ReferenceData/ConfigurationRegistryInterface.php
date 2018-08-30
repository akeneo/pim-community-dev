<?php

namespace Akeneo\Pim\Structure\Component\ReferenceData;

use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;

/**
 * Reference data configuration registry interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConfigurationRegistryInterface
{
    /**
     * @param ReferenceDataConfigurationInterface $configuration
     * @param string                              $name
     *
     * @return ConfigurationRegistryInterface
     */
    public function register(ReferenceDataConfigurationInterface $configuration, $name);

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
     * @return ReferenceDataConfigurationInterface
     */
    public function get($name);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * @return ReferenceDataConfigurationInterface[]
     */
    public function all();

    /**
     * @param string $name
     *
     * @return ConfigurationRegistryInterface
     */
    public function unregister($name);
}
