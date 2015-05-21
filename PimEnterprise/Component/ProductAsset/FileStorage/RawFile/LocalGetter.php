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
use League\Flysystem\Adapter\Local as LocalAdapter;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;

/**
 * Return the raw file of a file stored in a local filesystem
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class LocalGetter implements RawFileGetterInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(FileInterface $file, FilesystemInterface $filesystem)
    {
        if (!$filesystem->has($file->getPathname())) {
            throw new \LogicException('The file "%s" is not present on the filesystem.', $file->getPathname());
        }

        $adapter = $filesystem->getAdapter();
        if (!$adapter instanceof LocalAdapter) {
            throw new \LogicException('Unable to handle non "League\Flysystem\Adapter\Local" adapter.');
        }

        $localPathname = $adapter->applyPathPrefix($file->getPathname());

        if (!is_file($localPathname)) {
            throw new \LogicException('The file "%s" is not present on the local filesystem.', $localPathname);
        }

        return new \SplFileInfo($localPathname);
    }
}
