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
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\EntityToAddAssetsInto;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\MassUploadIntoAssetCollectionProcessor;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;

/**
 * Launches the asset upload processor to create assets from uploaded files
 * and add them to a product or a product model.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadIntoAssetCollectionTasklet implements TaskletInterface
{
    public const TASKLET_NAME = 'assets_mass_upload_into_asset_collection';

    /** @var StepExecution */
    protected $stepExecution;

    /** @var MassUploadIntoAssetCollectionProcessor */
    protected $massUploadToProductProcessor;

    /** @var MassUploadIntoAssetCollectionProcessor */
    protected $massUploadToProductModelProcessor;

    /** @var string */
    protected $tmpStorageDir;

    /**
     * @param MassUploadIntoAssetCollectionProcessor $massUploadToProductProcessor
     * @param MassUploadIntoAssetCollectionProcessor $massUploadToProductModelProcessor
     * @param string                                 $tmpStorageDir
     */
    public function __construct(
        MassUploadIntoAssetCollectionProcessor $massUploadToProductProcessor,
        MassUploadIntoAssetCollectionProcessor $massUploadToProductModelProcessor,
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
        $addAssetsTo = new EntityToAddAssetsInto(
            (int)$jobParameters->get('entity_id'),
            $jobParameters->get('attribute_code')
        );

        if ('product' === $entityType) {
            $processedItems = $this->massUploadToProductProcessor->applyMassUpload($uploadContext, $addAssetsTo);
            $this->incrementSummaryInfo($processedItems);
        } elseif ('product-model' === $entityType) {
            $processedItems = $this->massUploadToProductModelProcessor->applyMassUpload($uploadContext, $addAssetsTo);
            $this->incrementSummaryInfo($processedItems);
        }
    }

    /**
     * @param ProcessedItemList $processedItems
     */
    protected function incrementSummaryInfo(ProcessedItemList $processedItems): void
    {
        foreach ($processedItems as $item) {
            $file = $item->getItem();

            if (!$file instanceof \SplFileInfo && !$file instanceof EntityToAddAssetsInto) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expects a "\SplFileInfo", "%s" provided.',
                        ClassUtils::getClass($file)
                    )
                );
            }

            switch ($item->getState()) {
                case ProcessedItem::STATE_ERROR:
                    $this->stepExecution->incrementSummaryInfo('error');
                    $this->stepExecution->addError($item->getException()->getMessage());
                    break;
                case ProcessedItem::STATE_SKIPPED:
                    $this->stepExecution->incrementSummaryInfo('variations_not_generated');
                    $this->stepExecution->addWarning(
                        $item->getReason(),
                        [],
                        new DataInvalidItem(['filename' => $file->getFilename()])
                    );
                    break;
                default:
                    $this->stepExecution->incrementSummaryInfo($item->getReason());
                    break;
            }
        }
    }
}
