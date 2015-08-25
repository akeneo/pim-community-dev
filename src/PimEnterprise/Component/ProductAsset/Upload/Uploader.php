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

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Manage upload of an asset file
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class Uploader implements UploaderInterface
{
    /** @var string */
    const DIR_UPLOAD_TMP = 'mass_upload_tmp';

    /** @var string */
    const DIR_UPLOAD_SCHEDULED = 'mass_upload_scheduled';

    /** @var string */
    protected $uploadDirectory;

    /** @var string */
    protected $scheduledDirectory;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param TokenStorageInterface  $tokenStorage
     * @param RawFileStorerInterface $rawFileStorer
     * @param string                 $uploadDirectory
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        RawFileStorerInterface $rawFileStorer,
        $uploadDirectory
    ) {
        $this->rawFileStorer = $rawFileStorer;
        $this->tokenStorage  = $tokenStorage;

        $this->uploadDirectory    = $uploadDirectory . DIRECTORY_SEPARATOR . static::DIR_UPLOAD_TMP;
        $this->scheduledDirectory = $uploadDirectory . DIRECTORY_SEPARATOR . static::DIR_UPLOAD_SCHEDULED;
    }

    /**
     * {@inheritdoc}
     */
    public function upload(UploadedFile $file)
    {
        $filename  = $file->getClientOriginalName();
        $targetDir = $this->getUserUploadDir();

        return $file->move($targetDir, $filename);
    }

    /**
     * @return string
     */
    public function getUserUploadDir()
    {
        return $this->uploadDirectory . DIRECTORY_SEPARATOR . $this->getUsername();
    }

    /**
     * @return string
     */
    public function getUserScheduleDir()
    {
        return $this->scheduledDirectory . DIRECTORY_SEPARATOR . $this->getUsername();
    }

    /**
     * @return string
     */
    protected function getUsername()
    {
        return $this->tokenStorage->getToken()->getUser()->getUsername();
    }
}
