<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product;

use Pim\Bundle\CatalogBundle\Manager\AttributeValuesResolver;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\Flat\ProductAssociationFieldResolver;
use Pim\Component\Connector\ArrayConverter\Flat\ProductAttributeFieldExtractor;

/**
 * Product Flat Converter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertToStructuredField
{
    /** @var ProductAttributeFieldExtractor */
    protected $fieldExtractor;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var AttributeValuesResolver */
    protected $valuesResolver;

    /** @var ProductAssociationFieldResolver */
    protected $assocFieldResolver;

    /** @var array */
    protected $optionalAttributeFields;

    /** @var array */
    protected $optionalAssociationFields;

    /**
     * @param ProductAttributeFieldExtractor  $fieldExtractor
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param CurrencyRepositoryInterface     $currencyRepository
     * @param ProductAssociationFieldResolver $assocFieldResolver
     * @param AttributeValuesResolver         $valuesResolver
     */
    public function __construct(
        ProductAttributeFieldExtractor $fieldExtractor,
        AttributeRepositoryInterface $attributeRepository,
        CurrencyRepositoryInterface  $currencyRepository,
        ProductAssociationFieldResolver $assocFieldResolver,
        AttributeValuesResolver $valuesResolver
    ) {
        $this->fieldExtractor      = $fieldExtractor;
        $this->attributeRepository = $attributeRepository;
        $this->currencyRepository  = $currencyRepository;
        $this->assocFieldResolver  = $assocFieldResolver;
        $this->valuesResolver      = $valuesResolver;
    }

    /**
     * Convert a flat field to a structured one
     *
     * @param string $column The column name
     * @param string $value  The value in the cell
     *
     * @return array
     */
    public function convert($column, $value)
    {
        $associationFields = $this->assocFieldResolver->resolveAssociationFields();

        if (in_array($column, $associationFields)) {
            $value = $this->splitCollection($value);
            list($associationTypeCode, $associatedWith) = $this->splitFieldName($column);

            return ['associations' => [$associationTypeCode => [$associatedWith => $value]]];
        } elseif (in_array($column, ['categories', 'groups'])) {
            return [$column => $this->splitCollection($value)];
        } elseif ('enabled' === $column) {
            return [$column => (bool) $value];
        } elseif ('family' === $column) {
            return [$column => $value];
        } else {
            return $this->formatValue($column, $value);
        }
    }

    /**
     * Format a value cell
     *
     * @param string $column The column name
     * @param string $value  The value in the cell
     *
     * @return array
     */
    protected function formatValue($column, $value)
    {
        $fieldNameInfos = $this->fieldExtractor->extractAttributeFieldNameInfos($column);
        $data = $this->formatValueData($value, $fieldNameInfos);

        return [$fieldNameInfos['attribute']->getCode() => [[
            'locale' => $fieldNameInfos['locale_code'],
            'scope'  => $fieldNameInfos['scope_code'],
            'data'   => $data,
        ]]];
    }

    /**
     * Format the value data of a cell into a structured format
     *
     * prices:      '10 EUR, 24 USD'   => [{'data': '10', 'currency': 'EUR'}, {'data': '24', 'currency': 'USD'}]
     * metric:      '10 METER'         => {'data': 10, 'unit': 'METER'}
     * multiselect: 'red, blue, black' => ['red', 'blue', 'black']
     *
     * @param string $value          The value content
     * @param array  $fieldNameInfos The field informations
     *
     * @return array
     */
    protected function formatValueData($value, $fieldNameInfos)
    {
        switch ($fieldNameInfos['attribute']->getAttributeType()) {
            case 'pim_catalog_price_collection':
                $value = $this->splitCollection($value);

                $value = array_map(function ($priceValue) use ($fieldNameInfos) {
                    return $this->formatPrice($priceValue, $fieldNameInfos);
                }, $value);
                break;
            case 'pim_catalog_metric':
                $value = $this->formatMetric($value, $fieldNameInfos);
                break;
            case 'pim_catalog_multiselect':
                $value = $this->splitCollection($value);
                break;
            case 'pim_catalog_simpleselect':
                $value = $value === "" ? null : $value;
                break;
            case 'pim_catalog_boolean':
                $value = (bool) $value;
                break;
            case 'pim_catalog_number':
                $value = (float) $value;
                break;
            case 'pim_catalog_image':
            case 'pim_catalog_file':
                $value = $value === "" ? null : [
                    'filePath'         => $value,
                    'originalFilename' => basename($value)
                ];

                break;
        }

        return $value;
    }

    /**
     * Format price cell
     *
     * @param string $value          The value content
     * @param array  $fieldNameInfos The field informations
     *
     * @return array
     */
    protected function formatPrice($value, array $fieldNameInfos)
    {
        if ('' === $value) {
            return [];
        }

        //Due to the multiple column for price collections
        if (isset($fieldNameInfos['price_currency'])) {
            $currency = $fieldNameInfos['price_currency'];
        } else {
            list($value, $currency) = $this->splitUnitValue($value);
        }

        return ['data' => (float) $value, 'currency' => $currency];
    }

    /**
     * Format metric cell
     *
     * @param string $value          The value content
     * @param array  $fieldNameInfos The field informations
     *
     * @return array
     */
    protected function formatMetric($value, $fieldNameInfos)
    {
        if ('' === $value) {
            return ['data' => null, 'unit' => null];
        }

        //Due to the multi column format for metrics
        if (isset($fieldNameInfos['metric_unit'])) {
            $unit = $fieldNameInfos['metric_unit'];
        } else {
            list($value, $unit) = $this->splitUnitValue($value);
        }

        return ['data' => (float) $value, 'unit' => $unit];
    }

    /**
     * Split a collection in a flat value :
     *
     * '10 EUR, 24 USD' => ['10 EUR', '24 USD']
     *
     * @param string $value Raw value
     *
     * @return array
     */
    protected function splitCollection($value)
    {
        return '' === $value ? [] : explode(ProductAttributeFieldExtractor::ARRAY_SEPARATOR, $value);
    }

    /**
     * Split a field name:
     * 'description-en_US-mobile' => ['description', 'en_US', 'mobile']
     *
     * @param string $field Raw field name
     *
     * @return array
     */
    protected function splitFieldName($field)
    {
        return '' === $field ? [] : explode(ProductAttributeFieldExtractor::FIELD_SEPARATOR, $field);
    }

    /**
     * Split a value with it's unit/currency:
     * '10 EUR'   => ['10', 'EUR']
     * '10 METER' => ['10', 'METER']
     *
     * @param string $value Raw value
     *
     * @return array
     */
    protected function splitUnitValue($value)
    {
        return '' === $value ? [] : explode(ProductAttributeFieldExtractor::UNIT_SEPARATOR, $value);
    }
}
