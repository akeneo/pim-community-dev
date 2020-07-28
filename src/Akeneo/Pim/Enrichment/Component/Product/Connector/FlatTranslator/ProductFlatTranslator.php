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

        if ($translateHeaders) {
            $flatItemsByColumnName = $this->translateHeaders($flatItemsByColumnName, $locale);
        }

        $flatItems = $this->undoGroupFlatItemsByColumnName($flatItemsByColumnName);

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

    private function translateHeaders(array $flatItemsByColumnName, string $locale)
    {
        $attributeCodes = $this->extractAttributeCodes($flatItemsByColumnName);
        $associationTypes = $this->extractAssociationTypeCodes($flatItemsByColumnName);
        $quantifiedAssociationTypes = $this->extractQuantifiedAssociationTypeCodes($flatItemsByColumnName);

        $attributeTranslations = $this->getAttributeTranslations->byAttributeCodesAndLocale($attributeCodes, $locale);
        $associationTranslations = $this->getAssociationTypeTranslations->byAssociationTypeCodeAndLocale(
            array_merge($associationTypes, $quantifiedAssociationTypes),
            $locale
        );

        $results = [];
        foreach ($flatItemsByColumnName as $columnName => $flatItemValues) {
            $columnLabelized = sprintf('[%s]', $columnName);
            if ($this->isPropertyColumn($columnName)) {
                $columnLabelized = $this->labelTranslator->translate(
                    sprintf('pim_common.%s', $columnName),
                    $locale,
                    sprintf('[%s]', $columnName)
                );
            } elseif ($this->isAssociationColumn($columnName) || $this->isQuantifiedAssociationIdentifierColumn($columnName)) {
                list($associationType, $entityType) = explode('-', $columnName);
                $entityTypeLabelized =  $this->labelTranslator->translate(
                    sprintf('pim_common.%s', $entityType),
                    $locale,
                    sprintf('[%s]', $entityType)
                );

                $associationTypeLabelized = $associationTranslations[$associationType] ?? sprintf('[%s]', $associationType);
                $columnLabelized = sprintf('%s %s', $associationTypeLabelized, $entityTypeLabelized);
            } elseif ($this->isQuantifiedAssociationQuantityColumn($columnName)) {
                list($associationType, $entityType, $unit) = explode('-', $columnName);

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
            } elseif ($this->isAttributeColumn($columnName)) {
                $columnInformations = $this->attributeColumnInfoExtractor->extractColumnInfo($columnName);
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
                } elseif ($attribute->getType() === 'pim_catalog_metric' && strpos($columnName, '-unit') !== false) {
                    $metricLabelized = $this->labelTranslator->translate('pim_common.unit', $locale, '[unit]');

                    $columnLabelized = sprintf('%s (%s)', $columnLabelized, $metricLabelized);
                }
            }

            $results[$columnLabelized] = $flatItemValues;
        }


        return $results;
    }

    private function valueAreAllEmpty(array $values)
    {
        return count(array_filter($values, function ($value) {
            return $value !== '';
        })) === 0;
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

    private function extractAssociationTypeCodes(array $flatItemsByColumnName): array
    {
        $associationTypeCodes = [];
        foreach ($flatItemsByColumnName as $columnName => $flatItemValues) {
            if ($this->isAssociationColumn($columnName)) {
                list($quantifiedAssociationType, $entityType) = explode('-', $columnName);

                $associationTypeCodes[] = $quantifiedAssociationType;
            }
        }

        return array_unique($associationTypeCodes);
    }

    private function extractQuantifiedAssociationTypeCodes(array $flatItemsByColumnName): array
    {
        $quantifiedAssociationTypeCodes = [];
        foreach ($flatItemsByColumnName as $columnName => $flatItemValues) {
            if ($this->isQuantifiedAssociationIdentifierColumn($columnName)) {
                list($quantifiedAssociationType, $entityType) = explode('-', $columnName);

                $quantifiedAssociationTypeCodes[] = $quantifiedAssociationType;
            }
        }

        return array_unique($quantifiedAssociationTypeCodes);
    }

    private function extractAttributeCodes(array $flatItemsByColumnName): array
    {
        $attributeCodes = [];
        foreach ($flatItemsByColumnName as $columnName => $flatItems) {
            if ($this->isAttributeColumn($columnName)) {
                $columnInformations = $this->attributeColumnInfoExtractor->extractColumnInfo($columnName);

                $attributeCodes[] = $columnInformations['attribute']->getCode();
            }
        }

        return array_unique($attributeCodes);
    }

    private function isQuantifiedAssociationQuantityColumn($column)
    {
        $associationsColumns = $this->associationColumnsResolver->resolveQuantifiedQuantityAssociationColumns();

        return in_array($column, $associationsColumns);
    }


    /**
     * Internal pivoting to facilitate translation of flat items
     *
     * Before
     * [
     *   [
     *     'sku' => 1151511,
     *     'categories' => 'master_femme_chaussures_sandales'
     *     'description-en' => 'Ma description',
     *     'enabled' => 0,
     *     'groups' => 'group1',
     *     'name-fr_FR' => 'Sandales dorées Femme'
     *   ],
     *   [
     *     'sku' => 1151512,
     *     'categories' => 'master_femme_manteaux_manteaux_dhiver'
     *     'description-en' => 'Ma description1',
     *     'enabled' => 1,
     *     'groups' => 'group2,group3',
     *     'name-fr_FR' => 'Jupe imprimée Femme'
     *   ],
     * ]

     * After :
     * [
     *   'sku' => [
     *     1151511,
     *     1151512,
     *   ],
     *   'categories' => [
     *     'master_femme_chaussures_sandales',
     *     'master_femme_manteaux_manteaux_dhiver'
     *   ],
     *   'description-en' => [
     *     'Ma description',
     *     'Ma description1'
     *   ],
     *   'enabled' => [
     *     0,
     *     1
     *   ],
     *   'groups' => [
     *     'group1',
     *     'group2,group3'
     *   ],
     *   'name-fr_FR' => [
     *     'Sandales dorées Femme',
     *     'Jupe imprimée Femme'
     *   ]
     * ]
     *
     * @param array $flatItems
     * @return array
     */
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

    /**
     * Internal pivoting to facilitate translation of flat items
     *
     * Before :
     * [
     *   'sku' => [
     *     1151511,
     *     1151512,
     *   ],
     *   'categories' => [
     *     'Sandales femme',
     *     'Manteau d\'hiver'
     *   ],
     *   'description-en' => [
     *     'Ma description',
     *     'Ma description1'
     *   ],
     *   'enabled' => [
     *     'Non',
     *     'Oui',
     *   ],
     *   'groups' => [
     *     'Le group 1',
     *     'Le group 2,Le group 3'
     *   ],
     *   'name-fr_FR' => [
     *     'Sandales dorées Femme',
     *     'Jupe imprimée Femme'
     *   ]
     * ]
     *
     * After :
     * [
     *   [
     *     'sku' => 1151511,
     *     'categories' => 'Sandales femme'
     *     'description-en' => 'Ma description',
     *     'enabled' => 'Non',
     *     'groups' => 'Le group 1',
     *     'name-fr_FR' => 'Sandales dorées Femme'
     *   ],
     *   [
     *     'sku' => 1151512,
     *     'categories' => 'Manteau d\'hiver'
     *     'description-en' => 'Ma description1',
     *     'enabled' => 'Oui',
     *     'groups' => 'Le group 2,Le group 3',
     *     'name-fr_FR' => 'Jupe imprimée Femme'
     *   ],
     * ]
     *
     * @param array $columns
     * @return array
     */
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
