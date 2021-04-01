<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\FilterValues;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
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
    protected NormalizerInterface $normalizer;
    protected IdentifiableObjectRepositoryInterface $channelRepository;
    protected AttributeRepositoryInterface $attributeRepository;
    protected FillMissingValuesInterface $fillMissingProductModelValues;
    private ?GetProductsWithQualityScoresInterface $getProductsWithQualityScores;

    protected ?StepExecution $stepExecution = null;

    public function __construct(
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        FillMissingValuesInterface $fillMissingProductModelValues,
        ?GetProductsWithQualityScoresInterface $getProductsWithQualityScores = null
    ) {
        $this->normalizer          = $normalizer;
        $this->channelRepository   = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->fillMissingProductModelValues = $fillMissingProductModelValues;
        $this->getProductsWithQualityScores = $getProductsWithQualityScores;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product): array
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

        if (!$parameters->has('with_media') || true !== $parameters->get('with_media')) {
            $mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();
            $productStandard['values'] = array_filter(
                $productStandard['values'],
                function ($attributeCode) use ($mediaAttributes) {
                    return !in_array($attributeCode, $mediaAttributes);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        if (null !== $this->getProductsWithQualityScores && $product instanceof ProductInterface && $this->hasFilterOnQualityScore($parameters)) {
            $productStandard = $this->getProductsWithQualityScores->fromNormalizedProduct(
                $product->getIdentifier(),
                $productStandard,
                $structure['scope'] ?? null,
                $structure['locales'] ?? []
            );
        }

        return $productStandard;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
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
    protected function getAttributesCodesToFilter(JobParameters $parameters): array
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
    protected function areAttributesToFilter(JobParameters $parameters): bool
    {
        return isset($parameters->get('filters')['structure']['attributes'])
            && !empty($parameters->get('filters')['structure']['attributes']);
    }

    private function hasFilterOnQualityScore(JobParameters $parameters): bool
    {
        foreach ($parameters->get('filters')['data'] ?? [] as $filter) {
            $field = $filter['field'] ?? null;
            if ($field === 'quality_score_multi_locales') {
                return true;
            }
        }

        return false;
    }
}
