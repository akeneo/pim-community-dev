<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

/**
 * Metric attribute transformer
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricTransformer implements PropertyTransformerInterface
{
    public function transform($value, array $options = array())
    {
        if (empty($value)) {
            $metric = array();
        } else {
            if (false === strpos($value, ' ')) {
                throw new \InvalidArgumentException(
                    sprintf('Malformed metric: %s', $value)
                );
            }
            list($data, $unit) = explode(' ', $value);
            $metric = array(
                'data' => $data,
                'unit' => $unit,
            );
        }

        return $metric;
    }
}
