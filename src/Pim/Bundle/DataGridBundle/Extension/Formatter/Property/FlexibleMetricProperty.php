<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property;

/**
 * Flexible metric field property, able to render metric attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleMetricProperty extends FlexibleTwigProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $result = $this->getBackendData($value);
        $data   = $result['data'];
        $unit   = $result['unit'];

        if ($data && $unit) {
            return $this->getTemplate()->render(
                array(
                    'data' => $data,
                    'unit' => $unit
                )
            );
        }

        return null;
    }
}
