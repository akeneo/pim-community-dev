<?php

namespace Akeneo\Tool\Component\FileStorage;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

/**
 * File info factory, create a \Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileInfoFactoryInterface
{
    /**
     * @param \SplFileInfo $rawFile     the raw file to create a File with
     * @param string       $destFsAlias the filesystem alias where the file will be stored
     *
     * @return FileInfoInterface
     */
    public function createFromRawFile(\SplFileInfo $rawFile, $destFsAlias);
}
