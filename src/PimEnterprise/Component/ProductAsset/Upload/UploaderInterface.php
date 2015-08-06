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

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface UploaderInterface
{
    /**
     * Set asset upload subdirectory
     *
     * @param string $subDirectory
     */
    public function setSubDirectory($subDirectory);

    /**
     * @return string
     */
    public function getUserUploadDir();

    /**
     * @return string
     */
    public function getUserScheduleDir();

    /**
     * Extract asset code and locale from filename
     *
     * @param string $filename
     *
     * @return string[] Asset informations : ['code' => 'foo', 'locale' => 'en_US']
     */
    public function parseFilename($filename);

    /**
     * Move uploaded file in the file system
     *
     * @param UploadedFile $file
     */
    public function upload(UploadedFile $file);
}
