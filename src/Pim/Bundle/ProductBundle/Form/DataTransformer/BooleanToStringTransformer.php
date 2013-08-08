<?php

namespace Pim\Bundle\ProductBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transform boolean value into string
 * Allows that false will always be represented as "0" and true as "1".
 * (Otherwise false is represented as null)
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanToStringTransformer implements DataTransformerInterface
{
    /**
     * {@inheritDoc}
     */
    public function transform($value)
    {
        if (!is_bool($value)) {
            throw new TransformationFailedException('Expected a Boolean.');
        }

        return (string) (int) $value;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        if (!in_array($value, array(0, 1, '0', '1'), true)) {
            throw new TransformationFailedException('Expected a 0 or a 1.');
        }

        return (bool) $value;
    }
}
