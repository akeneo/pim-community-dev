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
     * @param string $name
     *
     * @return boolean
     */
    public function contains($name);

    /**
     * Returns an array of fixture infos for an array of file paths
     *
     * Each member of the returned array contains the following members :
     *   - name:       the name of the fixture
     *   - extension:  the extension of the fixture file
     *   - path:       the full path for the fixture file
     *
     * The returned fixture infos are ordered.
     *
     * @param string[] $filePaths
     *
     * @return array[]
     */
    public function getFixtures(array $filePaths);

    /**
     * Returns the loader class for the entity
     *
     * @param string $name
     *
     * @return int
     */
    public function getClass($name);

    /**
     * Returns true if the reader returns multiple files
     *
     * @param string $name
     *
     * @return boolean
     */
    public function isMultiple($name);

    /**
     * Returns the processor service for a given extension and configuration
     *
     * @param string $name
     * @param string $extension
     *
     * @return \Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface
     */
    public function getProcessor($name, $extension);

    /**
     * Returns the reader service for a given extension and configuration
     *
     * @param string $name
     * @param string $extension
     *
     * @return \Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface
     */
    public function getReader($name, $extension);
}
