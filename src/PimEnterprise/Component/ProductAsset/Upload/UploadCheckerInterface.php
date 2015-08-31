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

use PimEnterprise\Component\ProductAsset\Upload\Exception\UploadException;

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
     * Validate a filename before scheduling it
     *
     * @param string $filename       Filename to check
     * @param string $tmpUploadDir   Temporary directory for uploaded files
     * @param string $tmpScheduleDir Temporary directory for scheduled files
     *
     * @throws UploadException
     *
     * @return null
     */
    public function validateSchedule($filename, $tmpUploadDir, $tmpScheduleDir);
}
