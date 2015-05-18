<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage\FileHandler;

use PimEnterprise\Component\ProductAsset\Model\FileInterface;

/**
 * Move a file from a source filesystem to the storage destination filesystem
 * transforms it as a \PimEnterprise\Component\ProductAsset\Model\FileInterface
 * and save it to the database.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 *
 * TODO: could be moved in a dedicated FileStorage component
 */
interface FileHandlerInterface
{
    /**
     * Move a file from a source filesystem to the storage destination filesystem
     * transforms it as a \PimEnterprise\Component\ProductAsset\Model\FileInterface
     * and save it to the database.
     *
     * @param \SplFileInfo $file
     *
     * @return FileInterface
     */
    public function handle(\SplFileInfo $file);
}
