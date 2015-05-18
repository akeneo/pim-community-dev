<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage;

/**
 * List of the file systems that are used by the product asset component
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
final class ProductAssetFileSystems
{
    /**
     * Where files are uploaded by users
     * (typically /tmp on the local file system)
     */
    const FS_INCOMING_UPLOAD = 'incomingUpload';

    /**
     * Where files are dropped off before an import
     * (typically a directory on the local file system, /media/my/path/to/import)
     */
    const FS_INCOMING_IMPORT = 'incomingImport';

    /**
     * Where files are moved after a user upload or an import drop off.
     * When files are here, that means they have been stored in the database.
     * The files in the dropbox are not yet attached to an asset.
     * (typically a directory on the local file system, /media/dropbox/airlock/)
     */
    const FS_DROPBOX_AIRLOCK = 'dropboxAirlock';

    /**
     * Where files are moved after being processed from the airlock.
     * When files are here, that means the thumbnail has been created and the metadata have been extracted.
     * The files in the dropbox are not yet attached to an asset.
     * (typically a directory on the local file system, /media/dropbox/ready/)
     */
    const FS_DROPBOX_READY = 'dropboxReady';

    /**
     * Where frontend thumbnails are stored.
     * (typically a directory on the local file system, /%kernel_root_dir%/../web/thumbnails)
     */
    const FS_THUMBNAIL = 'thumbnail';
}
