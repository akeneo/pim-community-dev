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
    const DIR_UPLOAD_IMPORTED = 'mass_upload_imported';

    /** @var string */
    protected $uploadDirectory;

    /** @var string */
    protected $importDirectory;

    /** @var string */
    protected $username;

    /**
     * @param string $uploadDirectory The application temporary upload directory root
     * @param string $username
     */
    public function __construct($uploadDirectory, $username)
    {
        $this->uploadDirectory = $uploadDirectory . DIRECTORY_SEPARATOR . static::DIR_UPLOAD_TMP;
        $this->importDirectory = $uploadDirectory . DIRECTORY_SEPARATOR . static::DIR_UPLOAD_IMPORTED;

        if (empty($username)) {
            throw new \RuntimeException('Username must be set to initialize the upload context');
        }
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getTemporaryUploadDirectory()
    {
        return $this->uploadDirectory . DIRECTORY_SEPARATOR . $this->username;
    }

    /**
     * @return string
     */
    public function getTemporaryImportDirectory()
    {
        return $this->importDirectory . DIRECTORY_SEPARATOR . $this->username;
    }
}
