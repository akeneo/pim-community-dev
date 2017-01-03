<?php

namespace Pim\Bundle\ReferenceDataBundle\DataGrid\Datasource\ResultRecord\MongoDbOdm\Product;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Transform reference data
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataTransformer
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
        $properties = $attribute['properties'];

        if (isset($properties['reference_data_name']) && '' !== $properties['reference_data_name']) {
            $normalizedData = $result['normalizedData'];

            $fieldCode = ProductQueryUtility::getNormalizedValueField(
                $attributeCode,
                $attribute['localizable'],
                $attribute['scopable'],
                $locale,
                $scope
            );
            $backendType = $attribute['backendType'];
            $references = isset($normalizedData[$fieldCode]) ? $normalizedData[$fieldCode] : [];

            if (AttributeTypes::BACKEND_TYPE_REF_DATA_OPTION === $backendType) {
                $references = $this->filterOptionValues($references, $locale);
            } else {
                foreach ($references as $indexReference => $reference) {
                    $references[$indexReference] = $this->filterOptionValues($reference, $locale);
                }
            }

            $result[$attributeCode][$attribute['properties']['reference_data_name']] = $references;
        }

        return $result[$attributeCode];
    }

    /**
     * @param array  $reference
     * @param string $locale
     *
     * @return array $reference
     */
    protected function filterOptionValues($reference, $locale)
    {
        if (isset($reference['optionValues'])) {
            foreach (array_keys($reference['optionValues']) as $indexValue) {
                if ($indexValue !== $locale) {
                    unset($reference['optionValues'][$indexValue]);
                }
            }
        }

        return $reference;
    }
}
