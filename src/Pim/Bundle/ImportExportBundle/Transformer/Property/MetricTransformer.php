<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\FlexibleEntityBundle\Entity\Metric;
use Pim\Bundle\ImportExportBundle\Exception\InvalidValueException;

/**
 * Metric attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricTransformer implements PropertyTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        $value = trim($value);
        if (empty($value)) {
            $metric = null;
        } else {
            if (false === strpos($value, ' ')) {
                throw new InvalidValueException('Malformed metric: %value%', array('%value%'=>$value));
            }
            list($data, $unit) = preg_split('/ +/', $value);
            $metric = new Metric();
            $metric->setData($data)->setUnit($unit);
        }

        return $metric;
    }
}
