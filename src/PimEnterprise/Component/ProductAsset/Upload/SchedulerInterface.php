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
 * Schedule uploaded assets files for processing
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface SchedulerInterface
{
    /**
     * Schedule all uploaded files
     *
     * @param UploadContext $uploadContext
     *
     * @return array
     */
    public function schedule(UploadContext $uploadContext);

    /**
     * @param UploadContext $uploadContext
     *
     * @return \SplFileInfo[]
     */
    public function getScheduledFiles(UploadContext $uploadContext);
}
