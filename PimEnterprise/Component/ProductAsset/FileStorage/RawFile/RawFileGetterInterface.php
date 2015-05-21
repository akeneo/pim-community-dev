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

use League\Flysystem\FilesystemInterface;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;

/**
 * Get the raw file of a file stored in a virtual filesystem
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface RawFileGetterInterface
{
    /**
     * @param FileInterface       $file
     * @param FilesystemInterface $filesystem
     *
     * @return \SplFileInfo
     */
    public function get(FileInterface $file, FilesystemInterface $filesystem);
}
