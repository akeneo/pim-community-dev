<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileStorage;

use Akeneo\Component\FileStorage\Model\FileInterface;
use PimEnterprise\Component\ProductAsset\Exception\FileTransferException;

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
