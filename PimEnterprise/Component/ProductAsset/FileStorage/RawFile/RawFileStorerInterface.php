<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage\RawFile;

use PimEnterprise\Component\ProductAsset\Exception\DeletionFileException;
use PimEnterprise\Component\ProductAsset\Exception\TransferFileException;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;

/**
 * Move a raw file to the storage destination filesystem
 * transforms it as a \PimEnterprise\Component\ProductAsset\Model\FileInterface
 * and save it to the database.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 *
 * TODO: could be moved in a dedicated FileStorage component
 */
interface RawFileStorerInterface
{
    /**
     * Move a raw file to the storage destination filesystem
     * transforms it as a \PimEnterprise\Component\ProductAsset\Model\FileInterface
     * and save it to the database.
     *
     * @param \SplFileInfo $file        file to store
     * @param string       $destFsAlias alias of the destination filesystem
     *
     * @throws TransferFileException
     * @throws DeletionFileException
     *
     * @return FileInterface
     */
    public function store(\SplFileInfo $file, $destFsAlias);
}
