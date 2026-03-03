<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Family requirements validator
 *
 * This validator will check that:
 * - every requirement must have a list of requirement for every channel,
 * - a required attribute must be an attribute of a family.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRequirementsValidator extends ConstraintValidator
{
    public function __construct(
        protected AttributeRepositoryInterface $attributeRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($family, Constraint $constraint)
    {
        if (!$constraint instanceof FamilyRequirements) {
            throw new UnexpectedTypeException($constraint, FamilyRequirements::class);
        }

        if ($family instanceof FamilyInterface) {
            $this->validateRequiredAttributes($family, $constraint);
        }
    }

    /**
     * Validates that every required attribute is a family attribute.
     *
     * @param FamilyInterface    $family
     * @param FamilyRequirements $constraint
     */
    protected function validateRequiredAttributes(FamilyInterface $family, FamilyRequirements $constraint)
    {
        $familyAttributeCodes = $family->getAttributeCodes();

        foreach ($family->getAttributeRequirements() as $code => $attributeRequirement) {
            if ($attributeRequirement->isRequired() && !in_array($attributeRequirement->getAttributeCode(), $familyAttributeCodes)) {
                $this->context
                    ->buildViolation($constraint->messageAttribute, [
                        '%attribute%' => $attributeRequirement->getAttributeCode(),
                        '%channel%'   => $attributeRequirement->getChannelCode(),
                    ])
                    ->atPath($constraint->propertyPath)
                    ->addViolation();
            }
        }
    }
}
