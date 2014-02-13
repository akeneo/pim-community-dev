<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;

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

        return $value[$backend];

        // TODO : to refactor to add different backend type support
        if (is_object($value) && is_callable([$value, '__toString'])) {
            $value = $value->__toString();
        } elseif (false === $value) {
            return null;
        }

        return parent::convertValue($value);
    }
}
