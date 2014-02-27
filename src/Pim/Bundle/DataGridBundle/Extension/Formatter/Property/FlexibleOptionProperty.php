<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property;

/**
 * Flexible field property, able to render attribute type which use single option as backend
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleOptionProperty extends FlexibleFieldProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $data = $this->getBackendData($value);

        if (count($data['optionValues']) === 1) {
            return $data['optionValues'][0]['value'];
        }

        return isset($data['code']) ? sprintf('[%s]', $data['code']) : null;
    }
}
