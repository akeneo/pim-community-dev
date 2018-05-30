<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Validator\Constraints;

use Pim\Component\Catalog\Model\AttributeInterface;
use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for assets collection attribute
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AssetsCollectionValidator extends ConstraintValidator
{
    /**
     * Adds violations when an assets collection attribute is localizable or scopable.
     *
     * {@inheritdoc}
     */
    public function validate($attribute, Constraint $constraint)
    {
        if ($attribute instanceof AttributeInterface &&
            (AttributeTypes::ASSETS_COLLECTION === $attribute->getType()) &&
            ($attribute->isLocalizable() || $attribute->isScopable() || $attribute->isLocaleSpecific())) {
            $this->context->buildViolation($constraint->message, [
                '%attribute%' => $attribute->getCode()
            ])->addViolation();
        }
    }
}
