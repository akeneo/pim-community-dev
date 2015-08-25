<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\MassUpload;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\Upload\MassUploadProcessor;

/**
 * Launch the asset upload processor to create/update assets from uploaded files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MassUploadTasklet implements TaskletInterface
{
    /** @staticvar string */
    const TASKLET_NAME = 'asset_mass_upload';

    /** @var StepExecution */
    protected $stepExecution;

    /** @var MassUploadProcessor */
    protected $massUploadProcessor;

    /** @var NotificationManager */
    protected $notificationManager;

    /**
     * @param MassUploadProcessor $massUploadProcessor
     * @param NotificationManager $notificationManager
     */
    public function __construct(MassUploadProcessor $massUploadProcessor, NotificationManager $notificationManager)
    {
        $this->massUploadProcessor = $massUploadProcessor;
        $this->notificationManager = $notificationManager;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /*
     * {@inheritdoc}
     */
    public function execute(array $configuration)
    {
        $jobExecution = $this->stepExecution->getJobExecution();
        $username     = $jobExecution->getUser();

        $this->massUploadProcessor->getUploader()->setSubDirectory($username);

        $processedList = $this->massUploadProcessor->applyMassUpload();

        foreach ($processedList as $item) {
            $file = $item->getItem();

            if (!$file instanceof \SplFileInfo) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expects a "\SplFileInfo", "%s" provided.',
                        get_class($file)
                    )
                );
            }

            switch ($item->getState()) {
                case ProcessedItem::STATE_ERROR:
                    $this->stepExecution->incrementSummaryInfo('error');
                    $this->stepExecution->addError($item->getReason());
                    break;
                case ProcessedItem::STATE_SKIPPED:
                    $this->stepExecution->incrementSummaryInfo('skip');
                    $this->stepExecution->addWarning(self::TASKLET_NAME,
                        $item->getReason(),
                        [],
                        ['filename' => $file->getFilename()]
                    );
                    break;
                default:
                    $this->stepExecution->incrementSummaryInfo($item->getReason());
                    break;
            }
        }

        $this->notificationManager->notify(
            [$username],
            'pimee_product_asset.mass_upload.executed',
            'success',
            [
                'route'         => 'pim_enrich_job_tracker_show',
                'routeParams'   => ['id' => $jobExecution->getId()],
                'messageParams' => ['%label%' => $jobExecution->getJobInstance()->getLabel()],
                'context'       => ['actionType' => 'mass_upload']
            ]
        );
    }
}
