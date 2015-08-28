<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload;

/**
 * Manage the upload context:
 * - temporary directories
 * - username : can come from SF2 TokenStorage (web context) or a string username (batch context)
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class UploadContext
{
    /** @var string */
    const DIR_UPLOAD_TMP = 'mass_upload_tmp';

    /** @var string */
    const DIR_UPLOAD_SCHEDULED = 'mass_upload_scheduled';

    /** @var string */
    protected $uploadDirectory;

    /** @var string */
    protected $username;

    /**
     * @param string $uploadDirectory The application temporary upload directory root
     */
    public function __construct($uploadDirectory)
    {
        $this->uploadDirectory    = $uploadDirectory . DIRECTORY_SEPARATOR . static::DIR_UPLOAD_TMP;
        $this->scheduledDirectory = $uploadDirectory . DIRECTORY_SEPARATOR . static::DIR_UPLOAD_SCHEDULED;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getTemporaryUploadDirectory()
    {
        if (is_null($this->username)) {
            throw new \RuntimeException('Username must be set to initialize the upload context');
        }

        return $this->uploadDirectory . DIRECTORY_SEPARATOR . $this->username;
    }

    /**
     * @return string
     */
    public function getTemporaryScheduleDirectory()
    {
        if (is_null($this->username)) {
            throw new \RuntimeException('Username must be set to initialize the upload context');
        }

        return $this->scheduledDirectory . DIRECTORY_SEPARATOR . $this->username;
    }
}
