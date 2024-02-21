<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Symfony\Component\Form\Extension\Core\DataTransformer\BooleanToStringTransformer as BaseBooleanToStringTransformer;

/**
 * Boolean to string transformer that transforms an empty string or a '0' value to false
 * Allows updating a boolean value when a form is submitted with the $clearMissing parameter set to false
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanToStringTransformer extends BaseBooleanToStringTransformer
{
    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ('' === $value || '0' === $value) {
            return false;
        }

        return parent::reverseTransform($value);
    }
}
