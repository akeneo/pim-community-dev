<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Transform sub-part or product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsTransformer
{
    /**
     * @param array  $result
     * @param array  $attribute
     * @param string $locale
     * @param string $scope
     *
     * @return array
     */
    public function transform(array $result, array $attribute, $locale, $scope)
    {
        $attributeCode = $attribute['code'];
        $normalizedData = $result['normalizedData'];
        $fromNormData = [AttributeTypes::OPTION_SIMPLE_SELECT, AttributeTypes::OPTION_MULTI_SELECT];
        if (in_array($attribute['attributeType'], $fromNormData)) {
            $fieldCode = ProductQueryUtility::getNormalizedValueField(
                $attributeCode,
                $attribute['localizable'],
                $attribute['scopable'],
                $locale,
                $scope
            );
            $backendType = $attribute['backendType'];
            $options = isset($normalizedData[$fieldCode]) ? $normalizedData[$fieldCode] : [];

            if ($backendType === 'option') {
                $options = $this->filterOptionValues($options, $locale);
            } else {
                foreach ($options as $indexOption => $option) {
                    $options[$indexOption] = $this->filterOptionValues($option, $locale);
                }
            }

            $result[$attributeCode][$backendType] = $options;
        }

        return $result[$attributeCode];
    }

    /**
     * @param array  $option
     * @param string $locale
     *
     * @return array $option
     */
    protected function filterOptionValues($option, $locale)
    {
        if (isset($option['optionValues'])) {
            foreach (array_keys($option['optionValues']) as $indexValue) {
                if ($indexValue !== $locale) {
                    unset($option['optionValues'][$indexValue]);
                }
            }
        }

        return $option;
    }
}
