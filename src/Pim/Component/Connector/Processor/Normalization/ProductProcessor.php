<?php

namespace Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Connector\Processor\BulkMediaFetcher;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product processor to process and normalize entities to the standard format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkMediaFetcher */
    protected $mediaFetcher;

    /**
     * @param NormalizerInterface          $normalizer
     * @param ChannelRepositoryInterface   $channelRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductBuilderInterface      $productBuilder
     * @param ObjectDetacherInterface      $detacher
     * @param BulkMediaFetcher             $mediaFetcher
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductBuilderInterface $productBuilder,
        ObjectDetacherInterface $detacher,
        BulkMediaFetcher $mediaFetcher
    ) {
        $this->normalizer = $normalizer;
        $this->detacher = $detacher;
        $this->channelRepository = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productBuilder = $productBuilder;
        $this->mediaFetcher = $mediaFetcher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $structure = $parameters->get('filters')['structure'];
        $channel = $this->channelRepository->findOneByIdentifier($structure['scope']);
        $this->productBuilder->addMissingProductValues($product, [$channel], $channel->getLocales()->toArray());

        $productStandard = $this->normalizer->normalize($product, 'standard', [
            'channels' => [$channel->getCode()],
            'locales'  => array_intersect(
                $channel->getLocaleCodes(),
                $parameters->get('filters')['structure']['locales']
            ),
        ]);

        if ($this->areAttributesToFilter($parameters)) {
            $attributesToFilter = $this->getAttributesToFilter($parameters);
            $productStandard['values'] = $this->filterValues($productStandard['values'], $attributesToFilter);
        }

        if ($parameters->has('with_media') && $parameters->get('with_media')) {
            $directory = $this->stepExecution->getJobExecution()->getExecutionContext()
                ->get(JobInterface::WORKING_DIRECTORY_PARAMETER);

            $this->fetchMedia($product, $directory);
        } else {
            $mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();
            $productStandard['values'] = array_filter(
                $productStandard['values'],
                function ($attributeCode) use ($mediaAttributes) {
                    return !in_array($attributeCode, $mediaAttributes);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        $this->detacher->detach($product);

        return $productStandard;
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
     * @param ProductInterface $product
     * @param string           $directory
     */
    protected function fetchMedia(ProductInterface $product, $directory)
    {
        $identifier = $product->getIdentifier()->getData();
        $this->mediaFetcher->fetchAll($product->getValues(), $directory, $identifier);

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
    protected function filterValues(array $values, array $attributesToFilter)
    {
        $valuesToExport = [];
        foreach ($values as $code => $value) {
            if (in_array($code, $attributesToFilter)) {
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
    protected function getAttributesToFilter(JobParameters $parameters)
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
    protected function areAttributesToFilter(JobParameters $parameters)
    {
        return isset($parameters->get('filters')['structure']['attributes'])
            && !empty($parameters->get('filters')['structure']['attributes']);
    }
}
