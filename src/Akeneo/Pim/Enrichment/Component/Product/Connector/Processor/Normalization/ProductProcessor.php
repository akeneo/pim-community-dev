<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\FilterValues;
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
        private GetAttributes $getAttributes,
        private GetNormalizedQualityScoresInterface $getNormalizedQualityScores
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

        $withUuids = $parameters->has('with_uuid') ? $parameters->get('with_uuid') : false;
        $productStandard = $this->normalizer->normalize($product, 'standard', ['with_association_uuids' => $withUuids]);
        $jobLocales = $this->stepExecution->getJobParameters()->get('filters')['structure']['locales'];

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

        if ($this->hasFilterOnQualityScore($parameters)) {
            $productStandard['quality_scores'] = ($this->getNormalizedQualityScores)(
                $product instanceof ProductModelInterface ? $product->getCode() : $product->getUuid(),
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

    /**
     * It's possible to have a value not localizable, but locale specific.
     * In that case, it means the value is valid only for certain locales, but the locale is null.
     * So, it is necessary in that case to remove the attribute where the specific locales do not match
     * the configured job locales.
     */
    protected function filterLocaleSpecificAttributeCodes(array $attributeCodes, array $jobLocales): array
    {
        return array_filter($attributeCodes, function (string $attributeCode) use ($jobLocales) {
            $attribute = $this->getAttributes->forCode($attributeCode);

            if (null === $attribute) {
                return false;
            }

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
