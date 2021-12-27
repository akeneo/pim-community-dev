<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\AttributeIsAFamilyVariantAxisInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsVariantAxisWithoutAvailableLocalesValidator extends ConstraintValidator
{
    public function __construct(private AttributeIsAFamilyVariantAxisInterface $attributeIsAFamilyVariantAxis)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsVariantAxisWithoutAvailableLocales) {
            throw new UnexpectedTypeException($constraint, IsVariantAxisWithoutAvailableLocales::class);
        }

        if (!$value instanceof AttributeInterface || !is_string($value->getCode())) {
            return;
        }

        $isAFamilyVariantAxis = $this->attributeIsAFamilyVariantAxis->execute($value->getCode());

        if ($isAFamilyVariantAxis && $value->isLocaleSpecific()) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }
    }
}
