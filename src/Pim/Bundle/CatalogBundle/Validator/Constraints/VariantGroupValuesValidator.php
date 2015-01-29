<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
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
    /**
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Don't allow having axis or identifier as value in the product template
     *
     * @param object     $group
     * @param Constraint $constraint
     */
    public function validate($group, Constraint $constraint)
    {
        if ($group instanceof GroupInterface && $group->getType()->isVariant()) {
            if ($group->getProductTemplate() !== null) {
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
        $template = $variantGroup->getProductTemplate();
        $valuesData = $template->getValuesData();

        $forbiddenAttributeCodes = $this->attributeRepository->findUniqueAttributeCodes();
        foreach ($variantGroup->getAxisAttributes() as $axisAttribute) {
            $forbiddenAttributeCodes[] = $axisAttribute->getCode();
        }

        $invalidAttributeCodes = array_intersect($forbiddenAttributeCodes, array_keys($valuesData));

        if (count($invalidAttributeCodes) > 0) {
            $this->context->addViolation(
                $constraint->message,
                array(
                    '%group%'      => $variantGroup->getCode(),
                    '%attributes%' => $this->formatValues($invalidAttributeCodes)
                )
            );
        }
    }
}
