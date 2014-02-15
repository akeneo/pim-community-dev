<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Flexible field property, able to render majority of flexible attribute values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleFieldProperty extends FieldProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $backend = $value['attribute']['backendType'];
        $value   = $value[$backend];

        if ($backend === AbstractAttributeType::BACKEND_TYPE_PRICE) {
            $prices = [];
            foreach ($value as $price) {
                $prices[]= $price['data'].' '.$price['currency'];
            }
            $result = implode(', ', $prices);

        } elseif ($backend === AbstractAttributeType::BACKEND_TYPE_METRIC) {
            $result= $value['data'].' '.$value['unit'];

        } elseif ($backend === AbstractAttributeType::BACKEND_TYPE_OPTION) {
             $result= '['.$value['code'].']';

        } elseif ($backend === AbstractAttributeType::BACKEND_TYPE_OPTIONS) {
            $optionValues = [];
            foreach ($value as $option) {
                $optionValues[]= '['.$option['code'].']';
            }
            $result = implode(', ', $optionValues);

        } else {
            $result = $value;
        }

        return parent::convertValue($result);
    }
}
