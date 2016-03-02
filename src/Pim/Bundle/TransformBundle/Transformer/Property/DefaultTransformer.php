<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

/**
 * Default transformer for imports
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6
 */
class DefaultTransformer implements PropertyTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = [])
    {
        if (is_scalar($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        } else {
            return $value;
        }
    }
}
