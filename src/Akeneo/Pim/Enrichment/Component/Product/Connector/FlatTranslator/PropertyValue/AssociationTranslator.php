<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductLabelsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;

class AssociationTranslator
{
    private const PRODUCT_MODELS_COLUMN_SUFFIX = '-product_models';
    private const PRODUCTS_COLUMN_SUFFIX = '-products';
    private const GROUPS_ASSOCIATIONS_SUFFIX = '-groups';
    private const QUANTITY_ASSOCIATIONS_SUFFIX = '-quantity';

    /** @var AssociationColumnsResolver */
    private $associationColumnsResolver;

    /** @var GetProductModelLabelsInterface */
    private $getProductModelLabels;

    /** @var GetProductLabelsInterface */
    private $getProductLabels;

    /** @var GetGroupTranslations */
    private $getGroupTranslations;

    public function __construct(
        AssociationColumnsResolver $associationColumnsResolver,
        GetProductModelLabelsInterface $getProductModelLabels,
        GetProductLabelsInterface $getProductLabels,
        GetGroupTranslations $getGroupTranslations
    ) {
        $this->associationColumnsResolver = $associationColumnsResolver;
        $this->getProductModelLabels = $getProductModelLabels;
        $this->getProductLabels = $getProductLabels;
        $this->getGroupTranslations = $getGroupTranslations;
    }

    public function supports(string $columnName): bool
    {
        $associationsColumns = $this->associationColumnsResolver->resolveAssociationColumns();
        $quantifiedAssociationsColumns = $this->associationColumnsResolver->resolveQuantifiedAssociationColumns();

        return in_array($columnName, array_merge($associationsColumns, $quantifiedAssociationsColumns));
    }

    public function translate(string $columnName, array $values, string $locale, string $channel): array
    {
        if ($this->isColumnWithQuantity($columnName)) {
            return $values;
        }

        $translations = $this->getTranslations($values, $columnName, $locale, $channel);
        $result = $this->doTranslate($values, $translations);

        return $result;
    }

    private function getTranslations(array $values, string $columnName, string $locale, string $channel): array
    {
        $codes = $this->extractCodes($values);
        if ($this->isProductModelAssocation($columnName)) {
            $translations = $this->getProductModelLabels->byCodesAndLocaleAndScope($codes, $locale, $channel);
        } elseif ($this->isProductAssociation($columnName)) {
            $translations = $this->getProductLabels->byCodesAndLocaleAndScope($codes, $locale, $channel);
        } elseif ($this->isGroupAssociation($columnName)) {
            $translations = $this->getGroupTranslations->byGroupCodesAndLocale($codes, $locale);
        } else {
            throw new \LogicException(sprintf('Unsupported column to translate associations "%s"', $columnName));
        }

        return $translations;
    }

    private function extractCodes(array $values): array
    {
        $allCategoryCodes = [];
        foreach ($values as $value) {
            $categoryCodes = explode(',', $value);
            $categoryCodesWithoutQuantities = array_map(
                function (string $categoryCodeWithQuantities) {
                    return preg_replace('/\|.*$/', '', $categoryCodeWithQuantities);
                },
                $categoryCodes
            );
            $allCategoryCodes = array_merge($allCategoryCodes, $categoryCodesWithoutQuantities);
        }

        return array_unique($allCategoryCodes);
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
                preg_match('/^(?<code>.*)\|(?<quantity>.*)$/', $code, $matches);
                $quantity = $matches['quantity'] ?? null;
                if (empty($quantity)) {
                    $translation = $translations[$code] ??
                        sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $code);
                } else {
                    $code = $matches['code'];
                    $translationWithoutQuantity = $translations[$code] ??
                        sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $code);
                    $translation = sprintf('%s|%s', $translationWithoutQuantity, $quantity);
                }
                $labelized[] = $translation;
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

    private function isGroupAssociation(string $columnName): bool
    {
        return str_ends_with($columnName, self::GROUPS_ASSOCIATIONS_SUFFIX);
    }

    private function isColumnWithQuantity(string $columnName): bool
    {
        return str_ends_with($columnName, self::QUANTITY_ASSOCIATIONS_SUFFIX);
    }
}
