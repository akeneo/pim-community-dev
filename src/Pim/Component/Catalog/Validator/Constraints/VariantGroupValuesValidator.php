<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for variant group values constraint (forbid axis and identifier as product template values)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupValuesValidator extends ConstraintValidator
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Don't allow having axis or identifier as value in the product template
     *
     * {@inheritdoc}
     */
    public function validate($group, Constraint $constraint)
    {
        if ($group instanceof GroupInterface && $group->getType()->isVariant()) {
            if (null !== $group->getProductTemplate()) {
                $this->validateProductTemplateValues($group, $constraint);
            }
        }
    }

    /**
     * Validate variant group product template values
     *
     * @param GroupInterface $variantGroup
     * @param Constraint     $constraint
     */
    protected function validateProductTemplateValues(GroupInterface $variantGroup, Constraint $constraint)
    {
        $forbiddenAttrCodes = $this->attributeRepository->findUniqueAttributeCodes();
        foreach ($variantGroup->getAxisAttributes() as $axisAttribute) {
            $forbiddenAttrCodes[] = $axisAttribute->getCode();
        }

        $invalidAttrCodes = array_intersect(
            $forbiddenAttrCodes,
            $variantGroup->getProductTemplate()->getValues()->getAttributesKeys()
        );

        if (count($invalidAttrCodes) > 0) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '%group%'      => $variantGroup->getCode(),
                    '%attributes%' => $this->formatValues($invalidAttrCodes)
                ]
            )->addViolation();
        }
    }
}
