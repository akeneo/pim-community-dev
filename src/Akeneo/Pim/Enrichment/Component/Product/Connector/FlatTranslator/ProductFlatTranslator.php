<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeTranslator\AttributeFlatTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyTranslator\PropertyFlatTranslator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use Symfony\Component\Intl\Intl;

class ProductFlatTranslator implements FlatTranslatorInterface
{
    /**
     * @var AttributeColumnsResolver
     */
    private $attributeColumnsResolver;

    /**
     * @var AssociationColumnsResolver
     */
    private $associationColumnsResolver;

    /**
     * @var GetAttributeTranslations
     */
    private $getAttributeTranslations;

    /**
     * @var LabelTranslatorInterface
     */
    private $labelTranslator;

    /**
     * @var GetAssociationTypeTranslations
     */
    private $getAssociationTypeTranslations;

    /**
     * @var GetGroupTranslations
     */
    private $attributeColumnInfoExtractor;

    /**
     * @var PropertyTranslatorRegistry
     */
    private $propertyTranslationRegistry;

    /**
     * @var AttributeTranslatorRegistry
     */
    private $attributeTranslationRegistry;

    public function __construct(
        AttributeColumnsResolver $attributeColumnsResolver,
        AssociationColumnsResolver $associationColumnsResolver,
        GetAttributeTranslations $getAttributeTranslations,
        LabelTranslatorInterface $labelTranslator,
        GetAssociationTypeTranslations $getAssociationTypeTranslations,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        PropertyTranslatorRegistry $propertyTranslationRegistry,
        AttributeTranslatorRegistry $attributeTranslationRegistry
    ) {
        $this->attributeColumnsResolver = $attributeColumnsResolver;
        $this->associationColumnsResolver = $associationColumnsResolver;
        $this->getAttributeTranslations = $getAttributeTranslations;
        $this->labelTranslator = $labelTranslator;
        $this->getAssociationTypeTranslations = $getAssociationTypeTranslations;
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
        $this->propertyTranslationRegistry = $propertyTranslationRegistry;
        $this->attributeTranslationRegistry = $attributeTranslationRegistry;
    }

    public function translate(array $flatItems, string $locale, bool $translateHeaders): array
    {
        $translateHeaders = true;
        $flatItemsByColumnName = $this->groupFlatItemsByColumnName($flatItems);
        $flatItemsByColumnName = $this->translateValues($flatItemsByColumnName, $locale);
        $flatItems = $this->undoGroupFlatItemsByColumnName($flatItemsByColumnName);

        if ($translateHeaders) {
            $flatItems = $this->translateHeaders($flatItems, $locale);
        }

        return $flatItems;
    }

    public function translateValues(array $flatItemsByColumnName, string $locale): array
    {
        $result = [];
        foreach ($flatItemsByColumnName as $columnName => $values) {
            if ($this->valueAreAllEmpty($values)) {
                $result[$columnName] = $values;
                continue;
            }

            $propertyTranslation = $this->propertyTranslationRegistry->getTranslator($columnName);
            if ($propertyTranslation instanceof PropertyFlatTranslator) {
                $result[$columnName] = $propertyTranslation->translateValues($values, $locale);
                continue;
            }

            if ($this->attributeTranslationRegistry->support($columnName)) {
                $result[$columnName] = $this->attributeTranslationRegistry->translate($columnName, $values, $locale);
                continue;
            }

            $result[$columnName] = $values;
        }

        return $result;
    }

    private function valueAreAllEmpty(array $values)
    {
        return array_filter($values, function ($value) {
            return $value === '';
        });
    }

    private function translateHeaders(array $flatItems, string $locale)
    {
        $attributeCodes = $this->extractAttributeCodes($flatItems);
        $associationTypes = $this->extractAssociationTypeCodes($flatItems);
        $quantifiedAssociationTypes = $this->extractQuantifiedAssociationTypeCodes($flatItems);

        $attributeTranslations = $this->getAttributeTranslations->byAttributeCodesAndLocale($attributeCodes, $locale);
        $associationTranslations = $this->getAssociationTypeTranslations->byAssociationTypeCodeAndLocale(
            array_merge($associationTypes, $quantifiedAssociationTypes),
            $locale
        );

        $results = [];
        foreach ($flatItems as $flatItemIndex => $flatItem) {
            $result = [];
            $columns = array_keys($flatItem);
            foreach ($columns as $column) {
                $columnLabelized = $column;
                if ($this->isPropertyColumn($column)) {
                    $columnLabelized = $this->labelTranslator->translate(
                        sprintf('pim_common.%s', $column),
                        $locale,
                        sprintf('[%s]', $column)
                    );
                } elseif ($this->isAssociationColumn($column) || $this->isQuantifiedAssociationIdentifierColumn($column)) {
                    list($associationType, $entityType) = explode('-', $column);
                    $entityTypeLabelized =  $this->labelTranslator->translate(
                        sprintf('pim_common.%s', $entityType),
                        $locale,
                        sprintf('[%s]', $entityType)
                    );

                    $associationTypeLabelized = $associationTranslations[$associationType] ?? sprintf('[%s]', $associationType);
                    $columnLabelized = sprintf('%s %s', $associationTypeLabelized, $entityTypeLabelized);
                } elseif ($this->isQuantifiedAssociationQuantityColumn($column)) {
                    list($associationType, $entityType, $unit) = explode('-', $column);

                    $associationTypeLabelized = $associationTranslations[$associationType] ?? sprintf('[%s]', $associationType);
                    $entityTypeLabelized =  $this->labelTranslator->translate(
                        sprintf('pim_common.%s', $entityType),
                        $locale,
                        sprintf('[%s]', $entityType)
                    );

                    $unitLabelized =  $this->labelTranslator->translate(
                        'pim_common.unit',
                        $locale,
                        '([unit])'
                    );

                    $columnLabelized = sprintf('%s %s %s', $associationTypeLabelized, $entityTypeLabelized, $unitLabelized);
                } elseif ($this->isAttributeColumn($column)) {
                    $columnInformations = $this->attributeColumnInfoExtractor->extractColumnInfo($column);
                    $attribute = $columnInformations['attribute'];
                    $attributeCode = $attribute->getCode();

                    $columnLabelized = $attributeTranslations[$attributeCode] ?? sprintf('[%s]', $attributeCode);

                    $extraInformation = [];
                    if ($attribute->isLocalizable()) {
                        $extraInformation[] = $columnInformations['locale_code'];
                    }

                    if ($attribute->isScopable()) {
                        $extraInformation[] = $columnInformations['scope_code'];
                    }

                    if (!empty($extraInformation)) {
                        $columnLabelized = $columnLabelized . " (".implode(', ', $extraInformation) . ")";
                    }

                    if ($attribute->getType() === 'pim_catalog_price_collection') {
                        $language = \Locale::getPrimaryLanguage($locale);
                        $currencyLabelized = Intl::getCurrencyBundle()->getCurrencyName(
                            $columnInformations['price_currency'],
                            $language
                        );

                        $columnLabelized = sprintf('%s (%s)', $columnLabelized, $currencyLabelized);
                    } elseif ($attribute->getType() === 'pim_catalog_metric' && strpos($column, '-unit') !== false) {
                        $metricLabelized = $this->labelTranslator->translate('pim_common.unit', $locale, '[unit]');

                        $columnLabelized = sprintf('%s (%s)', $columnLabelized, $metricLabelized);
                    }
                }

                $result[$columnLabelized] = $flatItem[$column];
            }

            $results[$flatItemIndex] = $result;
        }

        return $results;
    }

    private function isAttributeColumn(string $column): bool
    {
        $attributeColumns = $this->attributeColumnsResolver->resolveAttributeColumns();

        return in_array($column, $attributeColumns);
    }

    private function isPropertyColumn(string $column): bool
    {
        return in_array($column, ['categories', 'enabled', 'family', 'parent', 'groups']);
    }

    private function isQuantifiedAssociationIdentifierColumn(string $column): bool
    {
        $quantifiedAssociationsColumns = $this->associationColumnsResolver->resolveQuantifiedIdentifierAssociationColumns();

        return in_array($column, $quantifiedAssociationsColumns);
    }

    private function isAssociationColumn(string $column): bool
    {
        $associationsColumns = $this->associationColumnsResolver->resolveAssociationColumns();

        return in_array($column, $associationsColumns);
    }

    private function extractAssociationTypeCodes(array $flatItems): array
    {
        $associationTypeCodes = [];
        foreach ($flatItems as $flatItem) {
            $columns = array_keys($flatItem);
            foreach ($columns as $column) {
                if ($this->isAssociationColumn($column)) {
                    list($quantifiedAssociationType, $entityType) = explode('-', $column);

                    $associationTypeCodes[] = $quantifiedAssociationType;
                }
            }
        }

        return array_unique($associationTypeCodes);
    }

    private function extractQuantifiedAssociationTypeCodes(array $flatItems): array
    {
        $quantifiedAssociationTypeCodes = [];
        foreach ($flatItems as $flatItem) {
            $columns = array_keys($flatItem);
            foreach ($columns as $column) {
                if ($this->isQuantifiedAssociationIdentifierColumn($column)) {
                    list($quantifiedAssociationType, $entityType) = explode('-', $column);

                    $quantifiedAssociationTypeCodes[] = $quantifiedAssociationType;
                }
            }
        }

        return array_unique($quantifiedAssociationTypeCodes);
    }

    private function extractAttributeCodes(array $flatItems): array
    {
        $attributeCodes = [];
        foreach ($flatItems as $flatItem) {
            $columns = array_keys($flatItem);
            foreach ($columns as $column) {
                if ($this->isAttributeColumn($column)) {
                    $columnInformations = $this->attributeColumnInfoExtractor->extractColumnInfo($column);

                    $attributeCodes[] = $columnInformations['attribute']->getCode();;
                }
            }
        }

        return array_unique($attributeCodes);
    }

    private function isQuantifiedAssociationQuantityColumn($column)
    {
        $associationsColumns = $this->associationColumnsResolver->resolveQuantifiedQuantityAssociationColumns();

        return in_array($column, $associationsColumns);
    }


    //@TODO to rename / in another service ?
    private function groupFlatItemsByColumnName(array $flatItems)
    {
        $result = array();
        foreach ($flatItems as $flatItemIndex => $flatItem) {
            foreach ($flatItem as $columnName => $value) {
                $result[$columnName][$flatItemIndex] = $value;
            }
        }

        return $result;
    }

    //@TODO to rename / in another service ?
    private function undoGroupFlatItemsByColumnName(array $columns)
    {
        $result = [];
        foreach($columns as $columnName => $columnValues) {
            foreach($columnValues as $flatItemIndex => $value) {
                $result[$flatItemIndex][$columnName] = $value;
            }
        }

        return $result;
    }
}
