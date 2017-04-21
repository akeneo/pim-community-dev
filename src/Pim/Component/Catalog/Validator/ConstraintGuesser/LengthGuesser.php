<?php

namespace Pim\Component\Catalog\Validator\ConstraintGuesser;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Guesser
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LengthGuesser implements ConstraintGuesserInterface
{
    /** @staticvar int */
    const TEXT_FIELD_LEMGTH = 255;

    /** @staticvar int */
    const TEXTAREA_FIELD_LEMGTH = 65535;

    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getType(),
            [
                AttributeTypes::TEXT,
                AttributeTypes::TEXTAREA
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        $constraints = [];

        $characterLimit = AttributeTypes::TEXTAREA === $attribute->getType() ?
            static::TEXTAREA_FIELD_LEMGTH :
            static::TEXT_FIELD_LEMGTH;

        if ($maxCharacters = $attribute->getMaxCharacters()) {
            $characterLimit = min($maxCharacters, $characterLimit);
        }

        $constraints[] = new Assert\Length(['max' => $characterLimit]);

        return $constraints;
    }
}
