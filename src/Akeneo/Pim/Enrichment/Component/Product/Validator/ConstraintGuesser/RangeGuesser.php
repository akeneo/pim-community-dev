<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Guesser
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getType(),
            [
                AttributeTypes::METRIC,
                AttributeTypes::NUMBER,
                AttributeTypes::DATE,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        $constraints = [];

        if (AttributeTypes::DATE === $attribute->getType()) {
            $min = $attribute->getDateMin();
            $max = $attribute->getDateMax();
        } else {
            $min = $attribute->getNumberMin();
            $max = $attribute->getNumberMax();
            if (false === $attribute->isNegativeAllowed() && ($min === null || $min < 0)) {
                $min = 0;
            }
        }

        if (null !== $min || null !== $max) {
            $constraints[] = new Range(['min' => $min, 'max' => $max]);
        }

        return $constraints;
    }
}
