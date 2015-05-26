<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage;

use PimEnterprise\Component\ProductAsset\Exception\FileTransferException;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;

/**
 * Move a file from a virtual filesystem to another
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface FileMoverInterface
{
    /**
     * @param FileInterface $file
     * @param string        $srcFsAlias
     * @param string        $destFsAlias
     *
     * @throws FileTransferException
     */
    public function move(FileInterface $file, $srcFsAlias, $destFsAlias);
}
