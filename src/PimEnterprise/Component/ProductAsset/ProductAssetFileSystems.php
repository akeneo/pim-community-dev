<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset;

/**
 * List of the file systems that are used by the product asset component
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
final class ProductAssetFileSystems
{
    const FS_PIM_THUMBNAIL = 'pimThumbnail';
    const FS_FILE_PROCESSING = 'damProcessing';
    const FS_STORAGE = 'storage';
}
