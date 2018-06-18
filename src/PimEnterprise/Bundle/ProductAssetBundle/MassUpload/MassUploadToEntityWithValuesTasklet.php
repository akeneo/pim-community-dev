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
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\AddAssetsTo;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\MassUploadIntoEntityWithValuesProcessor;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadToEntityWithValuesTasklet implements TaskletInterface
{
    public const TASKLET_NAME = 'assets_mass_upload_and_add_to_product';

    /** @var StepExecution */
    protected $stepExecution;

    /** @var MassUploadIntoEntityWithValuesProcessor */
    protected $massUploadToProductProcessor;

    /** @var MassUploadIntoEntityWithValuesProcessor */
    protected $massUploadToProductModelProcessor;

    /** @var string */
    protected $tmpStorageDir;

    /**
     * @param MassUploadIntoEntityWithValuesProcessor $massUploadToProductProcessor
     * @param MassUploadIntoEntityWithValuesProcessor $massUploadToProductModelProcessor
     * @param string                                  $tmpStorageDir
     */
    public function __construct(
        MassUploadIntoEntityWithValuesProcessor $massUploadToProductProcessor,
        MassUploadIntoEntityWithValuesProcessor $massUploadToProductModelProcessor,
        string $tmpStorageDir
    ) {
        $this->massUploadToProductProcessor = $massUploadToProductProcessor;
        $this->massUploadToProductModelProcessor = $massUploadToProductModelProcessor;
        $this->tmpStorageDir = $tmpStorageDir;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $jobExecution = $this->stepExecution->getJobExecution();

        $username = $jobExecution->getUser();
        $uploadContext = new UploadContext($this->tmpStorageDir, $username);

        $jobParameters = $jobExecution->getJobParameters();
        $entityType = $jobParameters->get('entity_type');
        $addAssetsTo = new AddAssetsTo((int)$jobParameters->get('entity_id'), $jobParameters->get('attribute_code'));

        if ('product' === $entityType) {
            $processedItems = $this->massUploadToProductProcessor->process($uploadContext, $addAssetsTo);
            $this->incrementSummaryInfo($processedItems, $this->stepExecution);
        } elseif ('product-model' === $entityType) {
            $processedItems = $this->massUploadToProductModelProcessor->process($uploadContext, $addAssetsTo);
            $this->incrementSummaryInfo($processedItems, $this->stepExecution);
        }
    }

    /**
     * @param ProcessedItemList $processedItems
     * @param StepExecution     $stepExecution
     */
    protected function incrementSummaryInfo(ProcessedItemList $processedItems, StepExecution $stepExecution): void
    {
        foreach ($processedItems as $item) {
            $file = $item->getItem();

            if (!$file instanceof \SplFileInfo && !$file instanceof AddAssetsTo) {
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
