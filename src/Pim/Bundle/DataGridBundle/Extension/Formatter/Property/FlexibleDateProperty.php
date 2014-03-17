<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;

/**
 * Flexible date property, able to render date value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleDateProperty extends FlexibleFieldProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $result = $this->getBackendData($value);
        if (get_class($result) === 'MongoDate') {
            $date = new \DateTime();
            $date->setTimestamp($result->sec);
            $result = $date;
        }

        return FieldProperty::convertValue($result);
    }
}
