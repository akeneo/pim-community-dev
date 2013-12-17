<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

/**
 * Interface for fixture loader configuration
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConfigurationRegistryInterface
{
    /**
     * Returns true if the registry contains configuration for the given name
     *
     * @var string $name
     *
     * @return boolean
     */
    public function contains($name);

    /**
     * Returns the fixture loading order for an entity
     *
     * @param string $name
     *
     * @return int
     */
    public function getOrder($name);

    /**
     * Returns the loader class for the entity
     *
     * @param string $name
     *
     * @return int
     */
    public function getClass($name);

    /**
     * Returns the processor service for a given extension and configuration
     *
     * @param string $name
     * @param string $extension
     *
     * @return \Oro\Bundle\BatchBundle\Item\ItemProcessorInterface
     */
    public function getProcessor($name, $extension);

    /**
     * Returns the reader service for a given extension and configuration
     *
     * @param string $name
     * @param string $extension
     *      *
     * @return \Oro\Bundle\BatchBundle\Item\ItemReaderInterface
     */
    public function getReader($name, $extension);
}
