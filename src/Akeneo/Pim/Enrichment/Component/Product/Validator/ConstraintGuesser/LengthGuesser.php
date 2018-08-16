<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
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
    const TEXT_FIELD_LENGTH = 255;

    /** @staticvar int */
    const TEXTAREA_FIELD_LENGTH = 65535;

    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getType(),
            [
                AttributeTypes::TEXT,
                AttributeTypes::TEXTAREA,
                AttributeTypes::IDENTIFIER
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
            static::TEXTAREA_FIELD_LENGTH :
            static::TEXT_FIELD_LENGTH;

        if ($maxCharacters = $attribute->getMaxCharacters()) {
            $characterLimit = min($maxCharacters, $characterLimit);
        }

        $constraints[] = new Assert\Length(['max' => $characterLimit]);

        return $constraints;
    }
}
