<?php

namespace Pim\Bundle\UIBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms numbers into integers
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IntegerTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return (double) $value;
    }

    /**
     * {@inheritdoc}
     *
     * If $value is integer we explicitly cast it as integer.
     * Otherwise we return value as it was.
     * $value can be a string so we have to check this case
     */
    public function transform($value)
    {
        return (is_numeric($value) && $value == floor($value))
            ? floor($value)
            : $value;
    }
}
