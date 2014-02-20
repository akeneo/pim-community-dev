<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Validator\Constraints\Range;

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
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            array(
                'pim_catalog_metric',
                'pim_catalog_number',
                'pim_catalog_date',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();

        if ('pim_catalog_date' === $attribute->getAttributeType()) {
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
            $constraints[] = new Range(array('min' => $min, 'max' => $max));
        }

        return $constraints;
    }
}
