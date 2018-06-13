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

namespace PimEnterprise\Bundle\ProductAssetBundle\MassUpload;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
abstract class AbstractMassUploadTasklet
{
    /**
     * @param ProcessedItemList $processedItems
     * @param StepExecution     $stepExecution
     */
    protected function incrementSummaryInfo(ProcessedItemList $processedItems, StepExecution $stepExecution): void
    {
        foreach ($processedItems as $item) {
            $file = $item->getItem();

            if (!$file instanceof \SplFileInfo) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expects a "\SplFileInfo", "%s" provided.',
                        ClassUtils::getClass($file)
                    )
                );
            }

            switch ($item->getState()) {
                case ProcessedItem::STATE_ERROR:
                    $stepExecution->incrementSummaryInfo('error');
                    $stepExecution->addError($item->getException()->getMessage());
                    break;
                case ProcessedItem::STATE_SKIPPED:
                    $stepExecution->incrementSummaryInfo('variations_not_generated');
                    $stepExecution->addWarning(
                        $item->getReason(),
                        [],
                        new DataInvalidItem(['filename' => $file->getFilename()])
                    );
                    break;
                default:
                    $stepExecution->incrementSummaryInfo($item->getReason());
                    break;
            }
        }
    }
}
