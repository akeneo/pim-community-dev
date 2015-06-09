<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileStorage\RawFile;

use Akeneo\Component\FileStorage\Model\FileInterface;
use PimEnterprise\Component\ProductAsset\Exception\FileRemovalException;
use PimEnterprise\Component\ProductAsset\Exception\FileTransferException;

/**
 * Move a raw file to the storage destination filesystem
 * transforms it as a \Akeneo\Component\FileStorage\Model\FileInterface
 * and save it to the database.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface RawFileStorerInterface
{
    /**
     * Move a raw file to the storage destination filesystem
     * transforms it as a \Akeneo\Component\FileStorage\Model\FileInterface
     * and save it to the database.
     *
     * @param \SplFileInfo $rawFile     file to store
     * @param string       $destFsAlias alias of the destination filesystem
     *
     * @throws FileTransferException
     * @throws FileRemovalException
     * @throws \Exception
     *
     * @return FileInterface
     */
    public function store(\SplFileInfo $rawFile, $destFsAlias);
}
