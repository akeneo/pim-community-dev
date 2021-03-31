<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Processor\Normalization;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\GetAssetMainMediaValuesInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class EntityWithAssetMediaProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private const MAX_ASSET_SEARCH_SIZE = 100;

    private ItemProcessorInterface $decoratedItemProcessor;
    private GetAssetMainMediaValuesInterface $getAssetMainMediaValues;
    private GetAttributes $getAttributes;
    private StepExecution $stepExecution;

    public function __construct(
        ItemProcessorInterface $itemProcessor,
        GetAssetMainMediaValuesInterface $getAssetMainMediaValues,
        GetAttributes $getAttributes
    ) {
        $this->decoratedItemProcessor = $itemProcessor;
        $this->getAssetMainMediaValues = $getAssetMainMediaValues;
        $this->getAttributes = $getAttributes;
    }

    /**
     * {@inheritDoc}
     */
    public function process($entity)
    {
        Assert::implementsInterface($entity, EntityWithValuesInterface::class);

        $entityStandard = $this->decoratedItemProcessor->process($entity);

        $parameters = $this->stepExecution->getJobParameters();
        if ($parameters->has('with_media') && $parameters->get('with_media')) {
            $entityStandard = $this->resolveAssetMainMedia(
                $entityStandard,
                $this->extractScopeCodesContext(),
                $this->extractLocaleCodesContext()
            );
        }

        return $entityStandard;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        if ($this->decoratedItemProcessor instanceof StepExecutionAwareInterface) {
            $this->decoratedItemProcessor->setStepExecution($this->stepExecution);
        }
    }

    private function extractScopeCodesContext(): ?array
    {
        $parameters = $this->stepExecution->getJobParameters();

        // Normal export
        $structure = $parameters->get('filters')['structure'] ?? [];
        if (isset($structure['scope'])) {
            return [$structure['scope']];
        }

        // Quick export
        $context = $parameters->get('filters')[0]['context'] ?? [];
        if (isset($context['scope'])) {
            return [$context['scope']];
        }

        return null;
    }

    /**
     * Only needed for "normal" export.
     * Quick export does not take in account the locale (= all values of localizable attributes are exported),
     * we don't take in account the locale in this case too.
     */
    private function extractLocaleCodesContext(): ?array
    {
        $parameters = $this->stepExecution->getJobParameters();

        return $parameters->get('filters')['structure']['locales'] ?? null;
    }

    private function resolveAssetMainMedia(array $entityStandard, ?array $scopeCodes, ?array $localeCodes): array
    {
        foreach ($entityStandard['values'] as $attributeCode => $values) {
            foreach ($values as $valueKey => $value) {
                if (!$this->productValueSatisfiesLocaleAndScopeFilters($value, $scopeCodes, $localeCodes)) {
                    continue;
                }

                $attribute = $this->getAttributes->forCode((string) $attributeCode);
                if (null === $attribute
                    || $attribute->type() !== AssetCollectionType::ASSET_COLLECTION
                    || empty($value['data'])
                ) {
                    continue;
                }

                $assetMainMediaValues = $this->getMainMediaValues(
                    $attribute->properties()['reference_data_name'],
                    $value['data']
                );

                $scopeCodesFilter = null !== $scopeCodes && null !== $value['scope'] ? [$value['scope']] : $scopeCodes;
                $localeCodesFilter = null !== $localeCodes && null !== $value['locale'] ? [$value['locale']] : $localeCodes;

                $filteredMainMedia = [];
                foreach ($assetMainMediaValues as $assetMainMediaValue) {
                    if ($this->assetValueSatisfiesLocaleAndScopeFilters(
                        $assetMainMediaValue,
                        $scopeCodesFilter,
                        $localeCodesFilter)
                    ) {
                        $filteredMainMedia[] = $assetMainMediaValue;
                    }
                }

                if (0 < count($filteredMainMedia)) {
                    $entityStandard['values'][$attributeCode][$valueKey]['paths'] = \array_map(
                        fn (array $mediaValue): string => $mediaValue['data']['filePath'] ?? $mediaValue['data'],
                        $filteredMainMedia
                    );
                }
            }
        }

        return $entityStandard;
    }

    private function getMainMediaValues(string $assetFamilyIdentifier, array $assetCodes): \Iterator
    {
        foreach (array_chunk($assetCodes, self::MAX_ASSET_SEARCH_SIZE) as $chunkedAssetCodes) {
            $assetMainMediaValuesPerAsset = $this->getAssetMainMediaValues->forAssetFamilyAndAssetCodes(
                $assetFamilyIdentifier,
                $chunkedAssetCodes
            );

            foreach ($assetMainMediaValuesPerAsset as $assetMainMediaValues) {
                foreach ($assetMainMediaValues as $assetMainMediaValue) {
                    yield $assetMainMediaValue;
                }
            }
        }
    }

    private function productValueSatisfiesLocaleAndScopeFilters(
        array $value,
        ?array $scopeCodes,
        ?array $localeCodes
    ): bool {
        return (null === $localeCodes || null === $value['locale'] || in_array($value['locale'], $localeCodes)) &&
            (null === $scopeCodes || null === $value['scope'] || in_array($value['scope'], $scopeCodes));
    }

    private function assetValueSatisfiesLocaleAndScopeFilters(
        array $value,
        ?array $scopeCodes,
        ?array $localeCodes
    ): bool {
        return (null === $localeCodes || null === $value['locale'] || in_array($value['locale'], $localeCodes)) &&
            (null === $scopeCodes || null == $value['channel'] || in_array($value['channel'], $scopeCodes));
    }
}
