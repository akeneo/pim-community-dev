<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
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
class RegexGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            [
                AttributeTypes::TEXT,
                AttributeTypes::IDENTIFIER,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        $constraints = [];

        if ('regexp' === $attribute->getValidationRule() && $pattern = $attribute->getValidationRegexp()) {
            $constraints[] = new Assert\Regex(['pattern' => $pattern]);
        }

        return $constraints;
    }
}
