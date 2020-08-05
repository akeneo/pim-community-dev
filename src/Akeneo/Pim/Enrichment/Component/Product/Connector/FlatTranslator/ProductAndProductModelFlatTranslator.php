<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatHeaderTranslator\HeaderTranslationContext;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class ProductAndProductModelFlatTranslator implements FlatTranslatorInterface
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
     * @var GetAssociationTypeTranslations
     */
    private $getAssociationTypeTranslations;

    /**
     * @var GetGroupTranslations
     */
    private $attributeColumnInfoExtractor;

    /**
     * @var FlatHeaderTranslatorRegistry
     */
    private $flatHeaderTranslatorRegistry;

    public function __construct(
        AttributeColumnsResolver $attributeColumnsResolver,
        AssociationColumnsResolver $associationColumnsResolver,
        GetAttributeTranslations $getAttributeTranslations,
        LabelTranslatorInterface $labelTranslator,
        GetAssociationTypeTranslations $getAssociationTypeTranslations,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        FlatHeaderTranslatorRegistry $flatHeaderTranslatorRegistry
    ) {
        $this->attributeColumnsResolver = $attributeColumnsResolver;
        $this->associationColumnsResolver = $associationColumnsResolver;
        $this->getAttributeTranslations = $getAttributeTranslations;
        $this->labelTranslator = $labelTranslator;
        $this->getAssociationTypeTranslations = $getAssociationTypeTranslations;
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
        $this->flatHeaderTranslatorRegistry = $flatHeaderTranslatorRegistry;
    }

    public function translate(array $flatItems, string $locale, string $scope, bool $translateHeaders): array
    {
        $flatItemsByColumnName = $this->groupFlatItemsByColumnName($flatItems);

        if ($translateHeaders) {
            $flatItemsByColumnName = $this->translateHeaders($flatItemsByColumnName, $locale);
        }

        $flatItems = $this->undoGroupFlatItemsByColumnName($flatItemsByColumnName);

        return $flatItems;
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

        $context = new HeaderTranslationContext($attributeTranslations, $associationTranslations);

        $results = [];
        foreach ($flatItemsByColumnName as $columnName => $flatItemValues) {
            $columnLabelized = sprintf("[%s]", $columnName);

            $translator = $this->flatHeaderTranslatorRegistry->getTranslator($columnName);
            if ($translator !== null) {
                $columnLabelized = $translator->translate($columnName, $locale, $context);
            }

            $results[$columnLabelized] = $flatItemValues;
        }

        return $results;
    }

    private function isAttributeColumn(string $column): bool
    {
        $attributeColumns = $this->attributeColumnsResolver->resolveAttributeColumns();

        return in_array($column, $attributeColumns);
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

    /**
     * Internal pivoting to facilitate translation of flat items
     *
     * Before
     * [
     *   0 => [
     *     'sku' => 1151511,
     *     'categories' => 'master_femme_chaussures_sandales'
     *     'description-en' => 'Ma description',
     *     'enabled' => 0,
     *     'groups' => 'group1',
     *     'name-fr_FR' => 'Sandales dorées Femme'
     *   ],
     *   1 => [
     *     'sku' => 1151512,
     *     'categories' => 'master_femme_manteaux_manteaux_dhiver'
     *     'description-en' => 'Ma description1',
     *     'enabled' => 1,
     *     'groups' => 'group2,group3',
     *     'name-fr_FR' => 'Jupe imprimée Femme',
     *     'collection' => 'summer_2016'
     *   ],
     * ]

     * After :
     * [
     *   'sku' => [
     *     0 => 1151511,
     *     1 => 1151512,
     *   ],
     *   'categories' => [
     *     0 => 'master_femme_chaussures_sandales',
     *     1 => 'master_femme_manteaux_manteaux_dhiver'
     *   ],
     *   'description-en' => [
     *     0 => 'Ma description',
     *     1=> 'Ma description1'
     *   ],
     *   'enabled' => [
     *     0 => 0,
     *     1 => 1
     *   ],
     *   'groups' => [
     *     0 => 'group1',
     *     1 => 'group2,group3'
     *   ],
     *   'name-fr_FR' => [
     *     0 => 'Sandales dorées Femme',
     *     1 => 'Jupe imprimée Femme'
     *   ],
     *   'collection' => [
     *     1 => 'summer_2016'
     *   ]
     * ]
     */
    //@TODO to rename / in another service ?
    private function groupFlatItemsByColumnName(array $flatItems): array
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
     *     0 => 1151511,
     *     1 => 1151512,
     *   ],
     *   'categories' => [
     *     0 => 'Sandales femme',
     *     1 => 'Manteau d\'hiver'
     *   ],
     *   'description-en' => [
     *     0 => 'Ma description',
     *     1 => 'Ma description1'
     *   ],
     *   'enabled' => [
     *     0 => 'Non',
     *     1 => 'Oui',
     *   ],
     *   'groups' => [
     *     0 => 'Le group 1',
     *     1 => 'Le group 2,Le group 3'
     *   ],
     *   'name-fr_FR' => [
     *     0 => 'Sandales dorées Femme',
     *     1 => 'Jupe imprimée Femme'
     *   ],
     *   'collection' => [
     *     1 => 'Eté 2016'
     *   ]
     * ]
     *
     * After :
     * [
     *   0 => [
     *     'sku' => 1151511,
     *     'categories' => 'Sandales femme'
     *     'description-en' => 'Ma description',
     *     'enabled' => 'Non',
     *     'groups' => 'Le group 1',
     *     'name-fr_FR' => 'Sandales dorées Femme'
     *   ],
     *   1 => [
     *     'sku' => 1151512,
     *     'categories' => 'Manteau d\'hiver'
     *     'description-en' => 'Ma description1',
     *     'enabled' => 'Oui',
     *     'groups' => 'Le group 2,Le group 3',
     *     'name-fr_FR' => 'Jupe imprimée Femme',
     *     'collection' => 'Eté 2016'
     *   ],
     * ]
     */
    //@TODO to rename / in another service ?
    private function undoGroupFlatItemsByColumnName(array $columns): array
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
