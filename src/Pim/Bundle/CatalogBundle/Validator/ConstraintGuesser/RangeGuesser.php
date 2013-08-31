<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Validator\Constraints\Range;

/**
 * Guesser
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
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
                'pim_product_price_collection',
                'pim_product_metric',
                'pim_product_number',
                'pim_product_date',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();

        if ('pim_product_date' === $attribute->getAttributeType()) {
            $min = $attribute->getDateMin();
            $max = $attribute->getDateMax();
        } else {
            $min = $attribute->getNumberMin();
            $max = $attribute->getNumberMax();
            if (false === $attribute->isNegativeAllowed()) {
                $min = 0;
            }
        }

        if (null !== $min || null !== $max) {
            $constraints[] = new Range(array('min' => $min, 'max' => $max,));
        }

        return $constraints;
    }
}
