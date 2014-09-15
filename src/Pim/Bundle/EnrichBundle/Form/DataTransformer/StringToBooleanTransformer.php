<?php

namespace Pim\Bundle\EnrichBundle\Form\DataTransformer;

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
     * Convert '0' string into a real false boolean
     *
     * @param string $enabled
     *
     * @return bool|null
     */
    public function transform($enabled)
    {
        if (null === $enabled) {
            return null;
        }

        if ('0' === $enabled) {
            return false;
        } else {
            return $enabled;
        }
    }

    /**
     * Convert '1' string into a real true boolean
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

        if ('1' === $enabled) {
            return true;
        } else {
            return $enabled;
        }
    }
}
