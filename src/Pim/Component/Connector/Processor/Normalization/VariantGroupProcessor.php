<?php

namespace Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Variant group export processor, allows to,
 *  - normalize variant groups and related values (media included)
 *  - return the normalized data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var BulkFileExporter */
    protected $mediaExporter;

    /** @var ObjectUpdaterInterface */
    protected $variantGroupUpdater;

    /**
     * @param NormalizerInterface     $normalizer
     * @param ObjectDetacherInterface $objectDetacher
     * @param BulkFileExporter        $mediaExporter
     * @param ObjectUpdaterInterface  $variantGroupUpdater
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ObjectDetacherInterface $objectDetacher,
        BulkFileExporter $mediaExporter,
        ObjectUpdaterInterface $variantGroupUpdater
    ) {
        $this->normalizer = $normalizer;
        $this->objectDetacher = $objectDetacher;
        $this->mediaExporter = $mediaExporter;
        $this->variantGroupUpdater = $variantGroupUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function process($variantGroup)
    {
        $variantGroupStandard = $this->normalizer->normalize($variantGroup, null, [
            'with_variant_group_values' => true,
            'identifier'                => $variantGroup->getCode(),
        ]);

        $parameters = $this->stepExecution->getJobParameters();
        $this->importMedia($variantGroup, $parameters);

        $this->objectDetacher->detach($variantGroup);

        return $variantGroupStandard;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Import media in local filesystem
     *
     * @param GroupInterface $variantGroup
     * @param JobParameters  $parameters
     */
    protected function importMedia(GroupInterface $variantGroup, JobParameters $parameters)
    {
        if (null === $productTemplate = $variantGroup->getProductTemplate()) {
            return;
        }

        $directory = dirname($parameters->get('filePath'));
        $identifier = $variantGroup->getCode();
        $this->variantGroupUpdater->update($variantGroup, ['values' => $productTemplate->getValuesData()]);

        $this->mediaExporter->exportAll($productTemplate->getValues(), $directory, $identifier);

        foreach ($this->mediaExporter->getErrors() as $error) {
            $this->stepExecution->addWarning($error['message'], [], $error['media']);
        }
    }
}
