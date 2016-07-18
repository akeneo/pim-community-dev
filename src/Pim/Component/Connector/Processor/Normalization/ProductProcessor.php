<?php

namespace Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product processor to process and normalize entities to the standard format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
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

    /** @var BulkFileExporter */
    protected $mediaExporter;

    /**
     * @param NormalizerInterface          $normalizer
     * @param ChannelRepositoryInterface   $channelRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductBuilderInterface      $productBuilder
     * @param ObjectDetacherInterface      $detacher
     * @param BulkFileExporter             $mediaExporter
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductBuilderInterface $productBuilder,
        ObjectDetacherInterface $detacher,
        BulkFileExporter $mediaExporter
    ) {
        $this->normalizer = $normalizer;
        $this->detacher = $detacher;
        $this->channelRepository = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productBuilder = $productBuilder;
        $this->mediaExporter = $mediaExporter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $structure =  $parameters->get('filters')['structure'];
        $channel = $this->channelRepository->findOneByIdentifier($structure['scope']);
        $this->productBuilder->addMissingProductValues($product, [$channel], $channel->getLocales()->toArray());

        $attributesToFilter = $this->getAttributesToFilter($parameters);
        $productStandard = $this->normalizer->normalize($product, 'json');
        $productStandard['values'] = $this->filterValues(
            $productStandard['values'],
            array_intersect($channel->getLocaleCodes(), $structure['locales']),
            $channel->getCode(),
            $attributesToFilter
        );

        if ($parameters->has('with_media') && $parameters->get('with_media')) {
            $this->importMedia($product, $parameters);
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
     * Import media on the local filesystem
     *
     * @param ProductInterface $product
     * @param JobParameters    $parameters
     */
    protected function importMedia(ProductInterface $product, JobParameters $parameters)
    {
        $directory = dirname($parameters->get('filePath'));
        $identifier = $product->getIdentifier()->getData();
        $this->mediaExporter->exportAll($product->getValues(), $directory, $identifier);

        foreach ($this->mediaExporter->getErrors() as $error) {
            $this->stepExecution->addWarning($error['message'], [], $error['media']);
        }
    }

    /**
     * Filters the attributes that have to be exported based on a product and a list of attributes
     *
     * @param array      $values
     * @param array      $localeCodes
     * @param string     $channelCode
     * @param array|null $attributesToFilter
     *
     * @return array
     */
    protected function filterValues(array $values, array $localeCodes, $channelCode, array $attributesToFilter = null)
    {
        $valuesToExport = [];
        foreach ($values as $code => $value) {
            if (null === $attributesToFilter || in_array($code, $attributesToFilter)) {
                $valuesToExport[$code] = array_filter(
                    $value,
                    function ($data) use ($channelCode, $localeCodes) {
                        $keepScope  = null === $data['scope'] || $data['scope'] === $channelCode;
                        $keepLocale = null === $data['locale'] || in_array($data['locale'], $localeCodes);

                        return $keepScope && $keepLocale;
                    }
                );
            }
        }

        return $valuesToExport;
    }

    /**
     * Return a list of attributes to export
     *
     * @param JobParameters $parameters
     *
     * @return array|null
     */
    protected function getAttributesToFilter(JobParameters $parameters)
    {
        $attributes = null;
        if (isset($parameters->get('filters')['structure']['attributes'])) {
            $attributes = $parameters->get('filters')['structure']['attributes'];
            $identifierCode = $this->attributeRepository->getIdentifierCode();
            if (!in_array($identifierCode, $attributes)) {
                $attributes[] = $identifierCode;
            }
        }

        return $attributes;
    }
}
