<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Pim\Component\Connector\Processor\BulkMediaFetcher;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product model processor to process and normalize productModel model to the standard format
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkMediaFetcher */
    protected $mediaFetcher;

    /** @var EntityWithFamilyValuesFillerInterface */
    protected $productModelValuesFiller;

    /**
     * @param NormalizerInterface                   $normalizer
     * @param AttributeRepositoryInterface          $attributeRepository
     * @param ObjectDetacherInterface               $detacher
     * @param BulkMediaFetcher                      $mediaFetcher
     * @param EntityWithFamilyValuesFillerInterface $productModelValuesFiller
     */
    public function __construct(
        NormalizerInterface $normalizer,
        AttributeRepositoryInterface $attributeRepository,
        ObjectDetacherInterface $detacher,
        BulkMediaFetcher $mediaFetcher,
        EntityWithFamilyValuesFillerInterface $productModelValuesFiller
    ) {
        $this->normalizer = $normalizer;
        $this->detacher = $detacher;
        $this->attributeRepository = $attributeRepository;
        $this->mediaFetcher = $mediaFetcher;
        $this->productModelValuesFiller = $productModelValuesFiller;
    }

    /**
     * {@inheritdoc}
     */
    public function process($productModel)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $this->productModelValuesFiller->fillMissingValues($productModel);
        $productModelStandard = $this->normalizer->normalize($productModel, 'standard');

        if ($parameters->has('with_media') && $parameters->get('with_media')) {
            $directory = $this->stepExecution->getJobExecution()->getExecutionContext()
                ->get(JobInterface::WORKING_DIRECTORY_PARAMETER);

            $this->fetchMedia($productModel, $directory);
        } else {
            $mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();
            $productModelStandard['values'] = array_filter(
                $productModelStandard['values'],
                function ($attributeCode) use ($mediaAttributes) {
                    return !in_array($attributeCode, $mediaAttributes);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        $this->detacher->detach($productModel);

        return $productModelStandard;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Fetch medias on the local filesystem
     *
     * @param ProductModelInterface $productModel
     * @param string                $directory
     */
    private function fetchMedia(ProductModelInterface $productModel, $directory)
    {
        $identifier = $productModel->getCode();
        $this->mediaFetcher->fetchAll($productModel->getValues(), $directory, $identifier);

        foreach ($this->mediaFetcher->getErrors() as $error) {
            $this->stepExecution->addWarning($error['message'], [], new DataInvalidItem($error['media']));
        }
    }
}
