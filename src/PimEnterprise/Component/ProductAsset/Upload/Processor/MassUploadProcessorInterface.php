<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload\Processor;

use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
interface MassUploadProcessorInterface
{
    /**
     * Processes all imported uploaded files.
     *
     * @param UploadContext $uploadContext
     *
     * @return ProcessedItemList
     */
    public function process(UploadContext $uploadContext): ProcessedItemList;
}
