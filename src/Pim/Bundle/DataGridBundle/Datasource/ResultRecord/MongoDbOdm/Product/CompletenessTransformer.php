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
class CompletenessTransformer
{
    /**
     * @param array  $result
     * @param string $locale
     * @param sring  $scope
     *
     * @return array
     */
    public function transform(array $result, $locale, $scope)
    {
        $normalizedData = $result[ProductQueryUtility::NORMALIZED_FIELD];

        $completenessCode = $scope.'-'.$locale;
        if (isset($normalizedData['completenesses'][$completenessCode])) {
            $result['ratio'] = number_format($normalizedData['completenesses'][$completenessCode], 0);
        } else {
            $result['ratio'] = null;
        }

        return $result;
    }
}
