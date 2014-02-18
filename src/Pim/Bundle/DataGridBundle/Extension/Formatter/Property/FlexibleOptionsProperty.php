<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property;

/**
 * Flexible field property, able to render attribute type which use options as backend
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleOptionsProperty extends FlexibleFieldProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $data = $this->getBackendData($value);

        $optionValues = [];
        foreach ($data as $option) {
            $optionValues[]= '['.$option['code'].']';
        }
        $result = implode(', ', $optionValues);

        return $result;
    }
}
