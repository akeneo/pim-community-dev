<?php

namespace Akeneo\Component\FileStorage\File;

use Akeneo\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;

/**
 * Move a raw file to the storage destination filesystem
 * transforms it as a \Akeneo\Component\FileStorage\Model\FileInfoInterface
 * and save it to the database.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileStorerInterface
{
    /**
     * Move a raw file to the storage destination filesystem
     * transforms it as a \Akeneo\Component\FileStorage\Model\FileInfoInterface
     * and save it to the database.
     *
     * @param \SplFileInfo $rawFile       file to store
     * @param string       $destFsAlias   alias of the destination filesystem
     * @param bool         $deleteRawFile should the raw file be deleted once stored in the VFS or not ?
     *
     * @throws FileTransferException
     * @throws FileRemovalException
     * @throws \Exception
     *
     * @return FileInfoInterface
     */
    public function store(\SplFileInfo $rawFile, $destFsAlias, $deleteRawFile = false);
}
