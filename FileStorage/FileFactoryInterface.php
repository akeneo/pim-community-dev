<?php

namespace Akeneo\Component\FileStorage;

use Akeneo\Component\FileStorage\Model\FileInterface;

/**
 * File factory, create a \Akeneo\Component\FileStorage\Model\FileInterface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileFactoryInterface
{
    /**
     * @param \SplFileInfo $rawFile
     * @param array        $pathInfo
     * @param string       $destFsAlias
     *
     * @return FileInterface
     */
    public function create(\SplFileInfo $rawFile, array $pathInfo, $destFsAlias);
}
