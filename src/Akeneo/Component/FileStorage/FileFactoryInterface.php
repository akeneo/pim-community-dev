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

/**
 * File factory, create a \Akeneo\Component\FileStorage\Model\FileInterface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
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
