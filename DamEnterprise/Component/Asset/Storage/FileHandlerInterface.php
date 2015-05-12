<?php

namespace DamEnterprise\Component\Asset\Storage;

use PimEnterprise\Component\ProductAsset\Model\FileInterface;

/**
 * Move an incoming file to the dropbox and save it to the database
 */
interface FileHandlerInterface
{
    /**
     * Move an incoming \SpliFileInfo to the dropbox,
     * transforms it as a \PimEnterprise\Component\ProductAsset\Model\FileInterface
     * and save it to the database.
     *
     * @param \SplFileInfo $file
     *
     * @return FileInterface
     */
    public function handle(\SplFileInfo $file);
}
