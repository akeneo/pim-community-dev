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
     */
    public function register(ReferenceDataConfigurationInterface $configuration, string $name): \Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;

    /**
     * @param array  $configuration
     * @param string $name
     */
    public function registerRaw(array $configuration, string $name): \Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;

    /**
     * @param string $name
     */
    public function get(string $name): \Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;

    /**
     * @param string $name
     */
    public function has(string $name): bool;

    /**
     * @return ReferenceDataConfigurationInterface[]
     */
    public function all(): array;

    /**
     * @param string $name
     */
    public function unregister(string $name): \Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
}
