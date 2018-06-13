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

use Akeneo\Component\Batch\Model\StepExecution;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Component\ProductAsset\Upload\Processor\MassUploadProcessorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadToProductTasklet extends AbstractMassUploadTasklet implements TaskletInterface
{
    public const TASKLET_NAME = 'assets_mass_upload_and_add_to_product';

    /**
     * @param MassUploadProcessorInterface $processor
     * @param string                       $tmpStorageDir
     */
    public function __construct(
        MassUploadProcessorInterface $processor,
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
        $this->doExecute();
    }
}
