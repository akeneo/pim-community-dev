<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Bundle\ReferenceDataBundle\DataGrid\Datasource\ResultRecord\MongoDbOdm\Product\ReferenceDataTransformer;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Transform sub-part or product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValuesTransformer
{
    /**
     * @param array  $result
     * @param array  $attributes
     * @param string $locale
     * @param string $scope
     *
     * @return array
     */
    public function transform(array $result, array $attributes, $locale, $scope)
    {
        $optionsTransformer = new OptionsTransformer();
        $refDataTransformer = new ReferenceDataTransformer();

        if (isset($result['values'])) {
            foreach ($result['values'] as $value) {
                $filterValueLocale = isset($value['locale']) && ($value['locale'] !== $locale);
                $filterValueScope  = isset($value['scope']) && ($value['scope'] !== $scope);
                $attributeId       = $value['attribute'];

                if (!$filterValueLocale && !$filterValueScope && isset($attributes[$attributeId])) {
                    $attribute              = $attributes[$attributeId];
                    $attributeCode          = $attribute['code'];
                    $value['attribute']     = $attribute;
                    $result[$attributeCode] = $value;
                    $result[$attributeCode] = $optionsTransformer->transform($result, $attribute, $locale, $scope);
                    $result[$attributeCode] = $refDataTransformer->transform($result, $attribute, $locale, $scope);
                    $result[$attributeCode] = $this->prepareDateData($result, $attribute);
                    $result[$attributeCode] = $this->prepareMediaData($result, $attribute, $locale, $scope);
                }
            }

            unset($result['values']);
        }

        return $result;
    }

    /**
     * @param array $result
     * @param array $attribute
     *
     * @return array
     */
    protected function prepareDateData(array $result, array $attribute)
    {
        $dateTransformer = new DateTimeTransformer();
        $attributeCode   = $attribute['code'];
        $backendType     = $attribute['backendType'];
        $value           = $result[$attributeCode];

        if (AttributeTypes::DATE === $attribute['attributeType'] && isset($value[$backendType])) {
            $mongoDate = $value[$backendType];
            $value[$backendType] = $dateTransformer->transform($mongoDate);
        }

        return $value;
    }

    /**
     * @param array $result
     * @param array $attribute
     * @param string $locale
     * @param string $scope
     *
     * @return array
     */
    protected function prepareMediaData(array $result, array $attribute, $locale, $scope)
    {
        $attributeCode = $attribute['code'];
        $backendType = $attribute['backendType'];
        $value = $result[$attributeCode];
        if (AttributeTypes::IMAGE === $attribute['attributeType'] && isset($value[$backendType])) {
            $normalizedData = $result['normalizedData'];
            $attributeCode = ProductQueryUtility::getNormalizedValueField(
                $attributeCode,
                $attribute['localizable'],
                $attribute['scopable'],
                $locale,
                $scope
            );
            $value[$backendType] = $normalizedData[$attributeCode];
        }

        return $value;
    }
}
