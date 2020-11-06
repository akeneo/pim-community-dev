<?php

namespace Akeneo\Tool\Component\Connector\Reader\File;

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
     * Get directory path. Can be the path of extracted zip archive or directory file path
     */
    public function getDirectoryPath(): string;

    /**
     * Returns file headers
     */
    public function getHeaders(): array;
}
