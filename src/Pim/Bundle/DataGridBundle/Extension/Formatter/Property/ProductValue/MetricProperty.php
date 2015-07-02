<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue;

/**
 * Metric field property, able to render metric attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricProperty extends TwigProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $result = $this->getBackendData($value);
        $data   = isset($result['data']) ? $result['data'] : null;
        $unit   = $result['unit'];

        if ($data && $unit) {
            return $this->getTemplate()->render(
                [
                    'data' => $data,
                    'unit' => $unit
                ]
            );
        }
    }
}
