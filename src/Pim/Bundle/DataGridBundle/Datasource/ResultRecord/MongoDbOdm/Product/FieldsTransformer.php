<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

/**
 * Transform sub-part or product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldsTransformer
{
    /**
     * @param array  $result
     * @param string $locale
     *
     * @return array
     */
    public function transform(array $result, $locale)
    {
        $result['id'] = $result['_id']->__toString();
        unset($result['_id']);
        $result['dataLocale'] = $locale;
        $dateTransformer = new DateTimeTransformer();
        $result['created'] = isset($result['created']) ? $dateTransformer->transform($result['created']) : null;
        $result['updated'] = isset($result['updated']) ? $dateTransformer->transform($result['updated']) : null;
        $result['enabled'] = isset($result['enabled']) ? $result['enabled'] : false;

        return $result;
    }
}
