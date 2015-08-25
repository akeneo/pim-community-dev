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
     * @return string
     */
    public function getUserUploadDir();

    /**
     * @return string
     */
    public function getUserScheduleDir();

    /**
     * Move uploaded file in the file system
     *
     * @param UploadedFile $file
     */
    public function upload(UploadedFile $file);
}
