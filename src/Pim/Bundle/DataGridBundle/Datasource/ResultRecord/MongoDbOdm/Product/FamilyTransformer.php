<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;

/**
 * Transform sub-part or product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyTransformer
{
    /**
     * @param array  $result
     * @param string $locale
     *
     * @return array
     */
    public function transform(array $result, $locale)
    {
        $normalizedData = $result[ProductQueryUtility::NORMALIZED_FIELD];

        if (isset($normalizedData['family'])) {
            $family = $normalizedData['family'];

            if (isset($family['labels'][$locale]) && '' !== $family['labels'][$locale]) {
                $result['familyLabel'] = $family['labels'][$locale];
            } else {
                $result['familyLabel'] = '['.$family['code'].']';
            }

            if (isset($family['attributeAsLabel']) && $family['attributeAsLabel'] !== null) {
                $attributeCode = $family['attributeAsLabel'];
                if (isset($result[$attributeCode])) {
                    $attributeAsLabel = $result[$attributeCode];
                    $backendType = $attributeAsLabel['attribute']['backendType'];
                    $result['productLabel'] = isset($attributeAsLabel[$backendType]) ?
                        $attributeAsLabel[$backendType] : null;
                } else {
                    $result['productLabel'] = null;
                }
            }
        }

        return $result;
    }
}
