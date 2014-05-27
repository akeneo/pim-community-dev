<?php

namespace Pim\Bundle\UIBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms numbers into integers
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IntegerTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value == floor($value) ? floor($value) : $value;
    }
}
