<?php

namespace Pim\Component\Catalog\Validator\ConstraintGuesser;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
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
            $attribute->getType(),
            [
                AttributeTypes::PRICE_COLLECTION,
            ]
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
        $currencyGuesser = new CurrencyGuesser();

        return [
            new All(
                [
                    'constraints' => array_merge(
                        [
                            new Type(
                                ['type' => 'Pim\Component\Catalog\Model\ProductPriceInterface']
                            ),
                        ],
                        $numericGuesser->guessConstraints($attribute),
                        $notDecimalGuesser->guessConstraints($attribute),
                        $rangeGuesser->guessConstraints($attribute),
                        $currencyGuesser->guessConstraints($attribute)
                    )
                ]
            )
        ];
    }
}
