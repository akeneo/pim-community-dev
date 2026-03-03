<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductLabelsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;

class AssociationTranslator
{
    private const PRODUCT_MODELS_COLUMN_SUFFIX = '-product_models';
    private const PRODUCTS_COLUMN_SUFFIX = '-products';
    private const PRODUCT_UUIDS_COLUMN_SUFFIX = '-product_uuids';
    private const GROUPS_ASSOCIATIONS_SUFFIX = '-groups';

    public function __construct(
        private readonly AssociationColumnsResolver $associationColumnsResolver,
        private readonly GetProductModelLabelsInterface $getProductModelLabels,
        private readonly GetProductLabelsInterface $getProductLabels,
        private readonly GetGroupTranslations $getGroupTranslations,
    ) {
    }

    public function supports(string $columnName): bool
    {
        $associationsColumns = $this->associationColumnsResolver->resolveAssociationColumns();
        $quantifiedAssociationsColumns = $this->associationColumnsResolver->resolveQuantifiedIdentifierAssociationColumns();

        return in_array($columnName, array_merge($associationsColumns, $quantifiedAssociationsColumns));
    }

    public function translate(string $columnName, array $values, string $locale, string $channel): array
    {
        $translations = $this->getTranslations($values, $columnName, $locale, $channel);
        $result = $this->doTranslate($values, $translations);

        return $result;
    }

    private function getTranslations(array $values, string $columnName, string $locale, string $channel): array
    {
        $codes = $this->extractCodes($values);
        if ($this->isProductModelAssocation($columnName)) {
            return $this->getProductModelLabels->byCodesAndLocaleAndScope($codes, $locale, $channel);
        }
        if ($this->isProductAssociation($columnName)) {
            return $this->getProductLabels->byIdentifiersAndLocaleAndScope($codes, $locale, $channel);
        }
        if ($this->isProductUuidAssociation($columnName)) {
            return $this->getProductLabels->byUuidsAndLocaleAndScope($codes, $locale, $channel);
        }
        if ($this->isGroupAssociation($columnName)) {
            return $this->getGroupTranslations->byGroupCodesAndLocale($codes, $locale);
        }

        throw new \LogicException(sprintf('Unsupported column to translate associations "%s"', $columnName));
    }

    private function extractCodes(array $values): array
    {
        $codes = [];
        foreach ($values as $value) {
            $codes = array_merge($codes, explode(',', $value));
        }

        return array_unique($codes);
    }

    private function doTranslate(array $values, array $translations): array
    {
        $result = [];
        foreach ($values as $valueIndex => $value) {
            if (empty($value)) {
                $result[$valueIndex] = $value;
                continue;
            }

            $codes = explode(',', $value);
            $labelized = [];

            foreach ($codes as $code) {
                $labelized[] = $translations[$code] ??
                    sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $code);
            }

            $result[$valueIndex] = implode(',', $labelized);
        }

        return $result;
    }

    private function isProductModelAssocation(string $columnName): bool
    {
        return false !== strpos($columnName, self::PRODUCT_MODELS_COLUMN_SUFFIX);
    }

    private function isProductAssociation(string $columnName): bool
    {
        return false !== strpos($columnName, self::PRODUCTS_COLUMN_SUFFIX);
    }

    private function isProductUuidAssociation(string $columnName): bool
    {
        return false !== strpos($columnName, self::PRODUCT_UUIDS_COLUMN_SUFFIX);
    }

    private function isGroupAssociation(string $columnName): bool
    {
        return str_ends_with($columnName, self::GROUPS_ASSOCIATIONS_SUFFIX);
    }
}
