<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\AttributeIsAFamilyVariantAxis;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsVariantAxisWithoutAvailableLocalesValidator extends ConstraintValidator
{
    public function __construct(private AttributeIsAFamilyVariantAxis $attributeIsAFamilyVariantAxis) {}

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsVariantAxisWithoutAvailableLocales) {
            throw new UnexpectedTypeException($constraint, IsVariantAxisWithoutAvailableLocales::class);
        }

        if (!$value instanceof AttributeInterface) {
            return;
        }

        $isLocaleSpecific = $value->getAvailableLocales()->count() > 0;
        $isAFamilyVariantAxis = $this->attributeIsAFamilyVariantAxis->execute($value->getCode());

        if ($isAFamilyVariantAxis && $isLocaleSpecific) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }
    }
}
