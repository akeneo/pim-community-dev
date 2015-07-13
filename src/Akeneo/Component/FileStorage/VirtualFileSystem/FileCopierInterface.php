<?php

namespace Akeneo\Component\FileStorage\VirtualFileSystem;

use Akeneo\Component\FileStorage\Exception\FileTransferException;

/**
 * Copy a file from a virtual filesystem to another.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileCopierInterface
{
    /**
     * @param string $srcFsAlias
     * @param string $srcKey
     * @param string $dstFsAlias
     * @param string $dstKey
     *
     * @throws FileTransferException
     */
    public function copy($srcFsAlias, $srcKey, $dstFsAlias, $dstKey = null);
}
