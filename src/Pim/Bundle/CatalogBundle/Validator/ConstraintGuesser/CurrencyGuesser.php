<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\Currency;

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
        return array(
            new Currency()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return AttributeTypes::PRICE_COLLECTION === $attribute->getAttributeType();
    }
}
