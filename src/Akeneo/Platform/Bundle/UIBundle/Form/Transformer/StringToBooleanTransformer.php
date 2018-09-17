<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms a string into real boolean
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StringToBooleanTransformer implements DataTransformerInterface
{
    /**
     * Convert booleans into a string
     *
     * @param bool $enabled
     *
     * @return null|string
     */
    public function transform($enabled)
    {
        if (null === $enabled) {
            return null;
        }

        return $enabled ? '1' : '0';
    }

    /**
     * Convert string into a boolean
     *
     * @param string $enabled
     *
     * @return bool|null
     */
    public function reverseTransform($enabled)
    {
        if (null === $enabled) {
            return null;
        }

        return (bool) $enabled;
    }
}
