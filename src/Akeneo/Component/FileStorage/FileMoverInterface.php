<?php

namespace Akeneo\Component\FileStorage;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\Model\FileInterface;

/**
 * Move a file from a virtual filesystem to another.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileMoverInterface
{
    /**
     * @param FileInterface $file
     * @param string        $srcFsAlias
     * @param string        $destFsAlias
     *
     * @throws FileTransferException
     *
     * @return FileInterface the file that has been moved
     */
    public function move(FileInterface $file, $srcFsAlias, $destFsAlias);
}
