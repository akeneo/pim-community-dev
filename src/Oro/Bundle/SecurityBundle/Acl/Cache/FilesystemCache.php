<?php

namespace Oro\Bundle\SecurityBundle\Acl\Cache;

use Doctrine\Common\Cache\FilesystemCache as BaseFilesystemCache;

/**
 * The aim of this class is just modify an algorithm is used to generate file names
 * to avoid very long file names. We can do not use additional sha256 encoding used
 * in the original FilesystemCache class because $id passed to getFilename is quite unique itself.
 */
class FilesystemCache extends BaseFilesystemCache
{
    /**
     * {@inheritdoc}
     */
    protected function getFilename($id)
    {
        $id = preg_replace('@[\\\/:"*?<>|]+@', '', $id);

        return $this->directory . DIRECTORY_SEPARATOR . $id . self::EXTENSION;
    }
}
