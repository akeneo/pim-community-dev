<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AssociationTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue\FlatPropertyValueTranslatorInterface;

class ProductAndProductModelFlatTranslator implements FlatTranslatorInterface
{
    /** @var HeaderRegistry */
    private $headerRegistry;

    /** @var PropertyValueRegistry */
    private $propertyValueRegistry;

    /** @var AttributeValuesFlatTranslator */
    private $attributeValuesFlatTranslator;

    /** @var AssociationTranslator */
    private $associationTranslator;

    public function __construct(
        HeaderRegistry $headerRegistry,
        PropertyValueRegistry $propertyValueRegistry,
        AttributeValuesFlatTranslator $attributeValuesFlatTranslator,
        AssociationTranslator $associationTranslator
    ) {
        $this->headerRegistry = $headerRegistry;
        $this->propertyValueRegistry = $propertyValueRegistry;
        $this->attributeValuesFlatTranslator = $attributeValuesFlatTranslator;
        $this->associationTranslator = $associationTranslator;
    }

    public function translate(array $flatItems, string $locale, string $scope, bool $translateHeaders): array
    {
        $flatItemsByColumnName = $this->groupFlatItemsByColumnName($flatItems);
        $flatItemsByColumnName = $this->translateValues($flatItemsByColumnName, $locale, $scope);

        if ($translateHeaders) {
            $flatItemsByColumnName = $this->translateHeaders($flatItemsByColumnName, $locale);
        }

        $flatItems = $this->undoGroupFlatItemsByColumnName($flatItemsByColumnName);

        return $flatItems;
    }

    private function translateHeaders(array $flatItemsByColumnName, string $locale): array
    {
        $this->headerRegistry->warmup(array_keys($flatItemsByColumnName), $locale);

        $results = [];
        foreach ($flatItemsByColumnName as $columnName => $flatItemValues) {
            $translator = $this->headerRegistry->getTranslator($columnName);
            $columnLabelized = null !== $translator ? $translator->translate($columnName, $locale) :
                sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $columnName);
            $results[$columnName . '--' . $columnLabelized] = $flatItemValues;
        }

        return $results;
    }

    public function translateValues(array $flatItemsByColumnName, string $locale, string $channel): array
    {
        $result = [];
        foreach ($flatItemsByColumnName as $columnName => $values) {
            if ($this->areValuesEmpty($values)) {
                $result[$columnName] = $values;
                continue;
            }

            $propertyValueTranslator = $this->propertyValueRegistry->getTranslator($columnName);
            if ($propertyValueTranslator instanceof FlatPropertyValueTranslatorInterface) {
                $result[$columnName] = $propertyValueTranslator->translate($values, $locale, $channel);
                continue;
            }

            if ($this->attributeValuesFlatTranslator->supports($columnName)) {
                $result[$columnName] = $this->attributeValuesFlatTranslator->translate($columnName, $values, $locale);
                continue;
            }

            if ($this->associationTranslator->supports($columnName)) {
                $result[$columnName] = $this->associationTranslator->translate($columnName, $values, $locale, $channel);
                continue;
            }

            $result[$columnName] = $values;
        }

        return $result;
    }

    private function areValuesEmpty(array $values): bool
    {
        return 0 === count(array_filter($values, function ($value) {
            return '' !== $value;
        }));
    }

    /**
     * Internal pivoting to facilitate translation of flat items
     *
     * Before
     * [
     *   0 => [
     *     'sku' => 1151511,
     *     'categories' => 'master_femme_chaussures_sandales',
     *     'description-en' => 'Ma description',
     *     'enabled' => 0,
     *     'groups' => 'group1',
     *     'name-fr_FR' => 'Sandales dorées Femme'
     *   ],
     *   1 => [
     *     'sku' => 1151512,
     *     'categories' => 'master_femme_manteaux_manteaux_dhiver',
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
     * Revert internal pivoting used to facilitate translation of flat items
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
    private function undoGroupFlatItemsByColumnName(array $columns): array
    {
        $result = [];
        foreach ($columns as $columnName => $columnValues) {
            foreach ($columnValues as $flatItemIndex => $value) {
                $result[$flatItemIndex][$columnName] = $value;
            }
        }

        return $result;
    }
}
