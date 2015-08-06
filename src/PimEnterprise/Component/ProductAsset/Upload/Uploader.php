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

    /** @var int */
    protected $subDirectory;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /**
     * @param RawFileStorerInterface $rawFileStorer
     * @param string                 $uploadDirectory
     */
    public function __construct(
        RawFileStorerInterface $rawFileStorer,
        $uploadDirectory
    ) {
        $this->rawFileStorer = $rawFileStorer;

        $this->uploadDirectory    = $uploadDirectory . DIRECTORY_SEPARATOR . static::DIR_UPLOAD_TMP;
        $this->scheduledDirectory = $uploadDirectory . DIRECTORY_SEPARATOR . static::DIR_UPLOAD_SCHEDULED;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubDirectory($subDirectory)
    {
        $this->subDirectory = $subDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function parseFilename($filename)
    {
        $parsed = [
            'code'   => null,
            'locale' => null,
        ];

        $patternCodePart   = '[a-zA-Z0-9_]+';
        $patternLocalePart = '[a-z]{2}_[A-Z]{2}';

        $pattern = sprintf('/^ (%s) (?:-(%s))? \.[^.]+$/x', $patternCodePart, $patternLocalePart);

        if (preg_match($pattern, $filename, $matches)) {
            $parsed['code']   = $matches[1];
            $parsed['locale'] = isset($matches[2]) ? $matches[2] : null;
        }

        return $parsed;
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
        return $this->uploadDirectory . DIRECTORY_SEPARATOR . $this->subDirectory;
    }

    /**
     * @return string
     */
    public function getUserScheduleDir()
    {
        return $this->scheduledDirectory . DIRECTORY_SEPARATOR . $this->subDirectory;
    }
}
