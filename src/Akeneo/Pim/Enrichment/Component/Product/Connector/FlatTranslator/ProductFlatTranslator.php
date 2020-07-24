<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;
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
     * @var GetCategoryTranslations
     */
    private $getCategoryTranslations;

    /**
     * @var GetFamilyTranslations
     */
    private $getFamilyTranslations;

    /**
     * @var GetGroupTranslations
     */
    private $getGroupTranslations;

    /**
     * @var GetGroupTranslations
     */
    private $attributeColumnInfoExtractor;

    public function __construct(
        AttributeColumnsResolver $attributeColumnsResolver,
        AssociationColumnsResolver $associationColumnsResolver,
        GetAttributeTranslations $getAttributeTranslations,
        LabelTranslatorInterface $labelTranslator,
        GetAssociationTypeTranslations $getAssociationTypeTranslations,
        GetCategoryTranslations $getCategoryTranslations,
        GetFamilyTranslations $getFamilyTranslations,
        GetGroupTranslations $getGroupTranslations,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor
    ) {
        $this->attributeColumnsResolver = $attributeColumnsResolver;
        $this->associationColumnsResolver = $associationColumnsResolver;
        $this->getAttributeTranslations = $getAttributeTranslations;
        $this->labelTranslator = $labelTranslator;
        $this->getAssociationTypeTranslations = $getAssociationTypeTranslations;
        $this->getCategoryTranslations = $getCategoryTranslations;
        $this->getFamilyTranslations = $getFamilyTranslations;
        $this->getGroupTranslations = $getGroupTranslations;
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
    }

    public function translate(array $flatItems, string $locale, bool $translateHeaders): array
    {
        $translateHeaders = true;
        $flatItems = $this->translateValues($flatItems, $locale);
        if ($translateHeaders) {
            $flatItems = $this->translateHeaders($flatItems, $locale);
        }

        return $flatItems;
    }

    public function translateValues(array $flatItems, string $locale): array
    {
        $categoryCodes = $this->extractCategoryCodes($flatItems);
        $familyCodes = $this->extractFamilyCodes($flatItems);
        $parentIdentifiers = $this->extractParentIdentifiers($flatItems);
        $groupCodes = $this->extractGroupCodes($flatItems);

        $categoryTranslations = $this->getCategoryTranslations->byCategoryCodesAndLocale($categoryCodes, $locale);
        $familyTranslations = $this->getFamilyTranslations->byFamilyCodesAndLocale($familyCodes, $locale);
        $groupTranslations = $this->getGroupTranslations->byGroupCodesAndLocale($groupCodes, $locale);

        $results = [];
        foreach ($flatItems as $flatItemIndex => $flatItem) {
            $result = [];
            $columns = array_keys($flatItem);
            foreach ($columns as $column) {
                $labelizedValue = $flatItem[$column];
                if ($column === 'categories') {
                    $categoryCodes = explode(',', $flatItem['categories']);
                    $categoriesLabelized = [];
                    foreach ($categoryCodes as $categoryCode) {
                        $categoriesLabelized[] = $categoryTranslations[$categoryCode] ?? sprintf('[%s]', $categoryCode);
                    }

                    $labelizedValue = implode(',', $categoriesLabelized);
                } else if ($column === 'family') {
                    $familyLabelized = $familyTranslations[$flatItem['family']] ?? sprintf('[%s]', $flatItem['family']);

                    $labelizedValue = $familyLabelized;
                } else if ($column === 'groups') {
                    $groupsCodes = explode(',', $flatItem['groups']);
                    $groupsLabelized = [];
                    foreach ($groupsCodes as $groupsCode) {
                        $groupsLabelized[] = $groupTranslations[$groupsCode] ?? sprintf('[%s]', $groupsCode);
                    }

                    $labelizedValue = implode(',', $groupsLabelized);
                } else if ($column === 'parent') {
                    $labelizedValue = $flatItem['parent']; //TODO
                } else if ($column === 'enabled') {
                    $labelizedValue = $flatItem['enabled'] ? $this->labelTranslator->translate('pim_common.yes', $locale, '[yes]') : $this->labelTranslator->translate('pim_common.no', $locale,'[no]');
                } else if ($this->isAttributeColumn($column)) {
                    $labelizedValue = $flatItem[$column]; //TODO
                }

                $result[$column] = $labelizedValue;
            }

            $results[$flatItemIndex] = $result;
        }

        return $results;
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
                    } elseif ($attribute->getType() === 'pim_catalog_metric') {
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

    private function extractCategoryCodes(array $flatItems)
    {
        $categories = [];
        foreach ($flatItems as $flatItem) {
            $categories = array_merge($categories, explode(',', $flatItem['categories']));
        }

        return array_unique($categories);
    }

    private function extractFamilyCodes(array $flatItems)
    {
        $familyCodes = [];
        foreach ($flatItems as $flatItem) {
            $familyCodes[] = $flatItem['family'];
        }

        return array_unique($familyCodes);
    }
    private function isAttributeColumn(string $column): bool
    {
        $attributeColumns = $this->attributeColumnsResolver->resolveAttributeColumns();

        return in_array($column, $attributeColumns);
    }

    private function isLabelizableAttributeValueColumn(string $column): bool
    {
        $attributeColumns = $this->attributeColumnsResolver->resolveAttributeColumnsByTypes([
            AttributeTypes::OPTION_SIMPLE_SELECT,
            AttributeTypes::OPTION_MULTI_SELECT,
            AttributeTypes::ASSET_COLLECTION,
            AttributeTypes::REFERENCE_ENTITY_COLLECTION,
            AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT,
            AttributeTypes::METRIC,
            AttributeTypes::PRICE_COLLECTION
        ]);

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

    private function extractParentIdentifiers(array $flatItems): array
    {
        $parentIdentifiers = [];
        foreach ($flatItems as $flatItem) {
            if (isset($flatItem['parent'])) {
                $parentIdentifiers[] = $flatItem['parent'];
            }
        }

        return array_unique($parentIdentifiers);
    }

    private function extractGroupCodes(array $flatItems): array
    {
        $groupCodes = [];
        foreach ($flatItems as $flatItem) {
            $groupCodes[] = $flatItem['groups'];
        }

        return array_unique($groupCodes);
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
}
