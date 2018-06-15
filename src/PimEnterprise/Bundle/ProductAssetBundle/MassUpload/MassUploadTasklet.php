<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\MassUpload;

use Akeneo\Component\Batch\Model\StepExecution;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\MassUploadProcessor;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;

/**
 * Launch the asset upload processor to create/update assets from uploaded files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MassUploadTasklet extends AbstractMassUploadTasklet implements TaskletInterface
{
    public const TASKLET_NAME = 'asset_mass_upload';

    /** @var StepExecution */
    protected $stepExecution;

    /** @var MassUploadProcessor */
    protected $processor;

    /** @var string */
    protected $tmpStorageDir;

    /**
     * @param MassUploadProcessor $processor
     * @param string              $tmpStorageDir
     */
    public function __construct(
        MassUploadProcessor $processor,
        string $tmpStorageDir
    ) {
        $this->processor = $processor;
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

        $processedItems = $this->processor->process($uploadContext);

        $this->incrementSummaryInfo($processedItems, $this->stepExecution);
    }
}
