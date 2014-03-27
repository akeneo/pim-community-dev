<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\OptionsTransformer;

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

        if (isset($result['values'])) {
            foreach ($result['values'] as $value) {
                $filterValueLocale = isset($value['locale']) && ($value['locale'] !== $locale);
                $filterValueScope = isset($value['scope']) && ($value['scope'] !== $scope);
                $attributeId = $value['attribute'];

                if (!$filterValueLocale && !$filterValueScope and isset($attributes[$attributeId])) {
                    $attribute = $attributes[$attributeId];
                    $attributeCode = $attribute['code'];
                    $value['attribute']= $attribute;
                    $result[$attributeCode]= $value;
                    $result[$attributeCode]= $optionsTransformer->transform($result, $attribute, $locale, $scope);
                    $result[$attributeCode]= $this->prepareDateData($result, $attribute);
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
        $attributeCode = $attribute['code'];
        $backendType = $attribute['backendType'];
        $value = $result[$attributeCode];

        if ($attribute['attributeType'] === 'pim_catalog_date' && isset($value[$backendType])) {
            $mongoDate = $value[$backendType];
            $value[$backendType]= $dateTransformer->transform($mongoDate);
        }

        return $value;
    }
}
