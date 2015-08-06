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

use SplFileInfo;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface SchedulerInterface
{
    /**
     * @param string $sourceDirectory
     */
    public function setSourceDirectory($sourceDirectory);

    /**
     * @param string $scheduleDirectory
     */
    public function setScheduleDirectory($scheduleDirectory);

    /**
     * Schedule all uploaded files
     *
     * @return array
     */
    public function schedule();

    /**
     * @return SplFileInfo[]
     */
    public function getScheduledFiles();
}
