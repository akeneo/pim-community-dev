<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Bundle\Sql\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Currency;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Guesser for price values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        return [
            new Currency()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return AttributeTypes::PRICE_COLLECTION === $attribute->getType();
    }
}
