<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

/**
 * Interface for file writer that supports archiving the results
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ArchivableWriterInterface
{
    /**
     * Return an array of files written by the writer in the following format:
     * array(
     *     '/full/path/to/the/file' => 'path/relative/to/the/export/directory'
     * )
     *
     * @return array
     */
    public function getWrittenFiles();
}
