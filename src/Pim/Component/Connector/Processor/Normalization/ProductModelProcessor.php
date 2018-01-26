<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

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
     * @param IdentifiableObjectRepositoryInterface            $channelRepository
     * @param AttributeRepositoryInterface          $attributeRepository
     * @param ObjectDetacherInterface               $detacher
     * @param BulkMediaFetcher                      $mediaFetcher
     * @param EntityWithFamilyValuesFillerInterface $productModelValuesFiller
     */
    public function __construct(
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        ObjectDetacherInterface $detacher,
        BulkMediaFetcher $mediaFetcher,
        EntityWithFamilyValuesFillerInterface $productModelValuesFiller
    ) {
        $this->normalizer = $normalizer;
        $this->channelRepository   = $channelRepository;
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
        $structure = $parameters->get('filters')['structure'];
        $channel = $this->channelRepository->findOneByIdentifier($structure['scope']);
        $this->productModelValuesFiller->fillMissingValues($productModel);
        $productModelStandard = $this->normalizer->normalize(
            $productModel,
            'standard',
            [
                'channels' => [$channel->getCode()],
                'locales'  => array_intersect(
                    $channel->getLocaleCodes(),
                    $parameters->get('filters')['structure']['locales']
                ),
            ]
        );

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

    /**
     * Filters the attributes that have to be exported based on a product and a list of attributes
     *
     * @param array $values
     * @param array $attributesToFilter
     *
     * @return array
     */
    private function filterValues(array $values, array $attributesToFilter)
    {
        $valuesToExport = [];
        $attributesToFilter = array_flip($attributesToFilter);
        foreach ($values as $code => $value) {
            if (isset($attributesToFilter[$code])) {
                $valuesToExport[$code] = $value;
            }
        }

        return $valuesToExport;
    }

    /**
     * Return a list of attributes to export
     *
     * @param JobParameters $parameters
     *
     * @return array
     */
    private function getAttributesToFilter(JobParameters $parameters)
    {
        $attributes = $parameters->get('filters')['structure']['attributes'];
        $identifierCode = $this->attributeRepository->getIdentifierCode();
        if (!in_array($identifierCode, $attributes)) {
            $attributes[] = $identifierCode;
        }

        return $attributes;
    }

    /**
     * Are there attributes to filters ?
     *
     * @param JobParameters $parameters
     *
     * @return bool
     */
    private function areAttributesToFilter(JobParameters $parameters)
    {
        return isset($parameters->get('filters')['structure']['attributes'])
            && !empty($parameters->get('filters')['structure']['attributes']);
    }
}
