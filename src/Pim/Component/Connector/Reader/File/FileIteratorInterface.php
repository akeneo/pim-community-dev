<?php

namespace Pim\Component\Connector\Reader\File;

/**
 * FileIterator interface
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileIteratorInterface extends \Iterator
{
    /**
     * Iterator has been initialized ?
     *
     * @return bool
     */
    public function isInitialized();

    /**
     * Reset iterator when there is multi import
     *
     * @return FileIteratorInterface
     */
    public function reset();

    /**
     * Set reader options
     *
     * @param array $options
     *
     * @return FileIteratorInterface
     */
    public function setReaderOptions(array $options = []);

    /**
     * Set file path
     *
     * @param string $filePath
     *
     * @return FileIteratorInterface
     */
    public function setFilePath($filePath);

    /**
     * Get directory path. Can be the path of extracted zip archive or directory file path
     *
     * @return string
     */
    public function getDirectoryPath();
}
