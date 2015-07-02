<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
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
            $attribute->getAttributeType(),
            [
                'pim_catalog_text',
                'pim_catalog_textarea',
                'pim_catalog_identifier',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        $constraints = [];

        $characterLimit = 'pim_catalog_textarea' === $attribute->getAttributeType() ?
            static::TEXTAREA_FIELD_LEMGTH :
            static::TEXT_FIELD_LEMGTH;

        if ($maxCharacters = $attribute->getMaxCharacters()) {
            $characterLimit = min($maxCharacters, $characterLimit);
        }

        $constraints[] = new Assert\Length(['max' => $characterLimit]);

        return $constraints;
    }
}
