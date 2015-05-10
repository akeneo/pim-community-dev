<?php

namespace DamEnterprise\Component\Asset;

// just the list of the file systems that are used by the asset component
final class AssetFileSystems
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
