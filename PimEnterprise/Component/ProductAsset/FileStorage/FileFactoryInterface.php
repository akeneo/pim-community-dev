<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage;

use PimEnterprise\Component\ProductAsset\Model\FileInterface;

/**
 * File factory, create a \PimEnterprise\Component\ProductAsset\Model\FileInterface
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
