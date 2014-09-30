<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Constraint guesser for price collections
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            array(
                'pim_catalog_price_collection',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        $notDecimalGuesser = new NotDecimalGuesser();
        $rangeGuesser = new RangeGuesser();
        $numericGuesser = new NumericGuesser();

        return array(
            new All(
                array(
                    'constraints' => array_merge(
                        array(
                            new Type(
                                array('type' => 'Pim\Bundle\CatalogBundle\Model\ProductPrice')
                            ),
                        ),
                        $numericGuesser->guessConstraints($attribute),
                        $notDecimalGuesser->guessConstraints($attribute),
                        $rangeGuesser->guessConstraints($attribute)
                    )
                )
            )
        );
    }
}
