<?php

namespace Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Connector\Processor\BulkMediaFetcher;
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
class VariantGroupProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var BulkMediaFetcher */
    protected $mediaFetcher;

    /**
     * @param NormalizerInterface     $normalizer
     * @param ObjectDetacherInterface $objectDetacher
     * @param BulkMediaFetcher        $mediaFetcher
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ObjectDetacherInterface $objectDetacher,
        BulkMediaFetcher $mediaFetcher
    ) {
        $this->normalizer = $normalizer;
        $this->objectDetacher = $objectDetacher;
        $this->mediaFetcher = $mediaFetcher;
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

        if ($parameters->has('with_media') && $parameters->get('with_media')) {
            $directory = $this->stepExecution->getJobExecution()->getExecutionContext()
                ->get(JobInterface::WORKING_DIRECTORY_PARAMETER);

            $this->fetchMedia($variantGroup, $directory);
        }

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
     * Fetch medias in local filesystem
     *
     * @param GroupInterface $variantGroup
     * @param string         $directory
     */
    protected function fetchMedia(GroupInterface $variantGroup, $directory)
    {
        if (null === $productTemplate = $variantGroup->getProductTemplate()) {
            return;
        }

        $identifier = $variantGroup->getCode();

        $this->mediaFetcher->fetchAll($productTemplate->getValues(), $directory, $identifier);

        foreach ($this->mediaFetcher->getErrors() as $error) {
            $this->stepExecution->addWarning($error['message'], [], new DataInvalidItem($error['media']));
        }
    }
}
