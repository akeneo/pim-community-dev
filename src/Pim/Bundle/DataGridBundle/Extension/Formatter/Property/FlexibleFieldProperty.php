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
        $result = $this->getBackendData($value);

        return parent::convertValue($result);
    }

    /**
     * Retrieve the relevant backend data from attribute configuration
     *
     * @param array $value
     *
     * @return array
     */
    protected function getBackendData($value)
    {
        $backend = $value['attribute']['backendType'];
        $value   = $value[$backend];

        return $value;
    }
}
