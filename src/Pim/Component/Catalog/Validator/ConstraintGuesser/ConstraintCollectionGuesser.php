<?php

namespace Pim\Component\Catalog\Validator\ConstraintGuesser;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
use Symfony\Component\Validator\Constraints\All;

/**
 * URL collection guesser.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConstraintCollectionGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return AttributeTypes::TEXT_COLLECTION === $attribute->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        $constraints = [];
        $guesser = null;

        if ('url' === $attribute->getValidationRule()) {
            $guesser = new UrlGuesser();
        } elseif ('regexp' === $attribute->getValidationRule() && $pattern = $attribute->getValidationRegexp()) {
            $guesser = new RegexGuesser();
        } elseif ('email' === $attribute->getValidationRule()) {
            $guesser = new EmailGuesser();
        }

        if (null !== $guesser) {
            return [
                new All(['constraints' => $guesser->guessConstraints($attribute)])
            ];
        }

        return $constraints;
    }
}
