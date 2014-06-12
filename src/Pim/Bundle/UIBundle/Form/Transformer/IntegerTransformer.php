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
     */
    public function transform($value)
    {
        return is_int($value) ? (int) $value : $value;
    }
}
