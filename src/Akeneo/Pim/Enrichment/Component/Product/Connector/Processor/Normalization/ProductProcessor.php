<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\FilterValues;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Processor\BulkMediaFetcher;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkMediaFetcher */
    protected $mediaFetcher;

    /** @var FillMissingValuesInterface */
    protected $fillMissingProductModelValues;

    private ?GetProductsWithQualityScoresInterface $getProductsWithQualityScores;

    public function __construct(
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        BulkMediaFetcher $mediaFetcher,
        FillMissingValuesInterface $fillMissingProductModelValues,
        // @fixme Nullable to manage that product models don't have scores
        ?GetProductsWithQualityScoresInterface $getProductsWithQualityScores = null
    ) {
        $this->normalizer          = $normalizer;
        $this->channelRepository   = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->mediaFetcher        = $mediaFetcher;
        $this->fillMissingProductModelValues = $fillMissingProductModelValues;
        $this->getProductsWithQualityScores = $getProductsWithQualityScores;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $structure = $parameters->get('filters')['structure'];
        $channel = $this->channelRepository->findOneByIdentifier($structure['scope']);

        $productStandard = $this->normalizer->normalize($product, 'standard');

        // not done for product as it fill missing product values at the end for performance purpose
        // not done yet for product model export so we have to do it
        if ($product instanceof ProductModelInterface) {
            $productStandard = $this->fillMissingProductModelValues->fromStandardFormat($productStandard);
        }

        $attributeCodes = $this->areAttributesToFilter($parameters) ? $this->getAttributesCodesToFilter($parameters) : [];

        $productStandard['values'] = FilterValues::create()
            ->filterByChannelCode($channel->getCode())
            ->filterByLocaleCodes(array_intersect($channel->getLocaleCodes(), $parameters->get('filters')['structure']['locales']))
            ->filterByAttributeCodes($attributeCodes)
            ->execute($productStandard['values']);

        $productStandard['values'] = $this->filterLocaleSpecificAttributes($productStandard['values']);

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

        // @fixme Find a better way to manage that product models don't have scores ?
        if ($product instanceof ProductInterface && null !== $this->getProductsWithQualityScores && $this->hasFilterOnQualityScore($parameters)) {
            $productStandard = $this->getProductsWithQualityScores->fromNormalizedProduct($productStandard, $structure['scope'] ?? null, $structure['locales'] ?? []);
        }

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
     * @param EntityWithFamilyInterface $product
     * @param string           $directory
     */
    protected function fetchMedia(EntityWithFamilyInterface $product, $directory)
    {
        $this->mediaFetcher->fetchAll($product->getValues(), $directory, $product->getIdentifier());

        foreach ($this->mediaFetcher->getErrors() as $error) {
            $this->stepExecution->addWarning($error['message'], [], new DataInvalidItem($error['media']));
        }
    }

    protected function filterLocaleSpecificAttributes(array $values): array
    {
        $valuesToExport = [];
        $jobLocales = $this->stepExecution->getJobParameters()->get('filters')['structure']['locales'];
        foreach ($values as $code => $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($code);
            if (!$attribute->isLocaleSpecific() || !empty(array_intersect($jobLocales, $attribute->getLocaleSpecificCodes()))) {
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
    protected function getAttributesCodesToFilter(JobParameters $parameters)
    {
        $attributes = $parameters->get('filters')['structure']['attributes'];
        $identifierCode = $this->attributeRepository->getIdentifierCode();
        if (!in_array($identifierCode, $attributes)) {
            $attributes[] = $identifierCode;
        }

        return $attributes;
    }

    /**
     * Are there attributes to filter?
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

    private function hasFilterOnQualityScore(JobParameters $parameters): bool
    {
        foreach ($parameters->get('filters')['data'] ?? [] as $filter) {
            // @fixme Use a constant ? or a parameter ?
            if ($filter['field'] ?? null === 'quality_score_multi_locales') {
                return true;
            }
        }

        return false;
    }
}
