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
 * Check uploaded files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface UploadCheckerInterface
{
    /**
     * Extract asset code and locale from filename
     *
     * @param string $filename
     *
     * @return string[] Asset informations : ['code' => 'foo', 'locale' => 'en_US']
     */
    public function parseFilename($filename);

    /**
     * Check the upload status for a filename
     *
     * @param string $filename       Filename to check
     * @param string $tmpUploadDir   Temporary directory for uploaded files
     * @param string $tmpScheduleDir Temporary directory for scheduled files
     *
     * @return string
     */
    public function checkFilename($filename, $tmpUploadDir, $tmpScheduleDir);

    /**
     * Check if upload status is an error
     *
     * @param string $uploadStatus
     *
     * @return bool
     */
    public function isError($uploadStatus);
}
