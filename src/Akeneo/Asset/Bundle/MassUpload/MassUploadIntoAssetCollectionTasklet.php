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

namespace Akeneo\Asset\Bundle\MassUpload;

use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\ProcessedItemList;
use Akeneo\Asset\Component\Upload\MassUpload\EntityToAddAssetsInto;
use Akeneo\Asset\Component\Upload\MassUpload\MassUploadIntoAssetCollectionProcessor;
use Akeneo\Asset\Component\Upload\UploadContext;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Doctrine\Common\Util\ClassUtils;

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
            $jobParameters->get('entity_identifier'),
            $jobParameters->get('attribute_code')
        );

        $importedFileNames = $jobParameters->get('imported_file_names');

        if ('product' === $entityType) {
            $processedItems = $this->massUploadToProductProcessor->applyMassUpload(
                $uploadContext,
                $addAssetsTo,
                $importedFileNames
            );
            $this->incrementSummaryInfo($processedItems);
        } elseif ('product_model' === $entityType) {
            $processedItems = $this->massUploadToProductModelProcessor->applyMassUpload(
                $uploadContext,
                $addAssetsTo,
                $importedFileNames
            );
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
