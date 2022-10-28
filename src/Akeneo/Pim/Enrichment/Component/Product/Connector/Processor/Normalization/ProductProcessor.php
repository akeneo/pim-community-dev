<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\FilterValues;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
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
    protected ?StepExecution $stepExecution = null;

    public function __construct(
        protected NormalizerInterface $normalizer,
        protected IdentifiableObjectRepositoryInterface $channelRepository,
        protected AttributeRepositoryInterface $attributeRepository,
        protected FillMissingValuesInterface $fillMissingProductModelValues,
        // TODO: pull up master => remove nullability
        private ?GetAttributes $getAttributes = null,
        private ?GetProductsWithQualityScoresInterface $getProductsWithQualityScores = null,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process($product): array
    {
        $parameters = $this->stepExecution->getJobParameters();
        $structure = $parameters->get('filters')['structure'];
        $channel = $this->channelRepository->findOneByIdentifier($structure['scope']);
        $jobLocales = $this->stepExecution->getJobParameters()->get('filters')['structure']['locales'];
        $productStandard = $this->normalizer->normalize($product, 'standard');

        // not done for product as it fill missing product values at the end for performance purpose
        // not done yet for product model export so we have to do it
        if ($product instanceof ProductModelInterface) {
            $productStandard = $this->fillMissingProductModelValues->fromStandardFormat($productStandard);
        }

        $attributeCodes = $this->areAttributesToFilter($parameters) ? $this->getAttributesCodesToFilter($parameters) : array_keys($productStandard['values']);
        $attributeCodes = $this->filterLocaleSpecificAttributeCodes($attributeCodes, $jobLocales);
        if (!$parameters->has('with_media') || true !== $parameters->get('with_media')) {
            $attributeCodes = array_diff($attributeCodes, $this->attributeRepository->findMediaAttributeCodes());
        }

        $productStandard['values'] = FilterValues::create()
            ->filterByChannelCode($channel->getCode())
            ->filterByLocaleCodes(array_intersect($channel->getLocaleCodes(), $parameters->get('filters')['structure']['locales']))
            ->filterByAttributeCodes($attributeCodes)
            ->execute($productStandard['values']);

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

    // TODO: pull up master => Remove this function
    protected function filterLocaleSpecificAttributes(array $values): array
    {
        if ($this->getAttributes === null) {
            return $values;
        }

        $valuesToExport = [];
        $jobLocales = $this->stepExecution->getJobParameters()->get('filters')['structure']['locales'];
        foreach ($values as $code => $value) {
            $attribute = $this->getAttributes->forCode($code);
            if (!$attribute->isLocaleSpecific()
                || !empty(array_intersect($jobLocales, $attribute->availableLocaleCodes()))) {
                $valuesToExport[$code] = $value;
            }
        }

        return $valuesToExport;
    }

    /**
     * It's possible to have a value not localizable, but locale specific.
     * In that case, it means the value is valid only for certain locales, but the locale is null.
     * So, it is necessary in that case to remove the attribute where the specific locales do not match
     * the configured job locales.
     */
    protected function filterLocaleSpecificAttributeCodes(array $attributeCodes, array $jobLocales): array
    {
        // TODO: remove after merge into master
        if ($this->getAttributes === null) {
            return $attributeCodes;
        }

        return array_filter($attributeCodes, function (string $attributeCode) use ($jobLocales) {
            $attribute = $this->getAttributes->forCode($attributeCode);
            if (!$attribute->isLocaleSpecific()) {
                return true;
            }

            return !empty(array_intersect($jobLocales, $attribute->availableLocaleCodes()));
        });
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
