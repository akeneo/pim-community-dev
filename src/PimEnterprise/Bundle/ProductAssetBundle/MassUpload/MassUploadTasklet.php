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
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\Upload\MassUploadProcessor;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;

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

    /** @var string */
    protected $tmpStorageDir;

    /** @var NotificationManager */
    protected $notificationManager;

    /**
     * @param MassUploadProcessor $massUploadProcessor
     * @param NotificationManager $notificationManager
     * @param string              $tmpStorageDir
     */
    public function __construct(
        MassUploadProcessor $massUploadProcessor,
        NotificationManager $notificationManager,
        $tmpStorageDir
    ) {
        $this->massUploadProcessor = $massUploadProcessor;
        $this->notificationManager = $notificationManager;
        $this->tmpStorageDir       = $tmpStorageDir;
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

        $username      = $jobExecution->getUser();
        $uploadContext = new UploadContext($this->tmpStorageDir, $username);

        $processedList = $this->massUploadProcessor->applyMassUpload($uploadContext);

        foreach ($processedList as $item) {
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
                    $this->stepExecution->incrementSummaryInfo('error');
                    $this->stepExecution->addError($item->getException()->getMessage());
                    break;
                case ProcessedItem::STATE_SKIPPED:
                    $this->stepExecution->incrementSummaryInfo('variations_not_generated');
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
                'routeParams'   => ['id'         => $jobExecution->getId()],
                'messageParams' => ['%label%'    => $jobExecution->getJobInstance()->getLabel()],
                'context'       => ['actionType' => 'mass_upload']
            ]
        );
    }
}
