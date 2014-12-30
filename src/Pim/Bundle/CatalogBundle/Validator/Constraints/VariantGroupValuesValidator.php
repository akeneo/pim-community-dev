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
     * @param object     $variantGroup
     * @param Constraint $constraint
     */
    public function validate($variantGroup, Constraint $constraint)
    {
        /** @var GroupInterface */
        if ($variantGroup instanceof GroupInterface && $variantGroup->getType()->isVariant()) {
            if ($variantGroup->getProductTemplate() !== null) {
                $this->validateProductValues($variantGroup, $constraint);
            }
        }
    }

    /**
     * Validate variant group product values
     *
     * @param GroupInterface $variantGroup
     * @param Constraint     $constraint
     */
    protected function validateProductValues(GroupInterface $variantGroup, Constraint $constraint)
    {
        $template = $variantGroup->getProductTemplate();
        $forbiddenAttributes = $variantGroup->getAttributes()->toArray();
        $forbiddenAttributes[] = $this->attributeRepository->getIdentifier();

        $notValidAttributes = [];
        foreach ($forbiddenAttributes as $attribute) {
            if ($template->hasValueForAttribute($attribute)) {
                $notValidAttributes[] = $attribute->getCode();
            }
        }

        if (count($notValidAttributes) > 0) {
            $this->context->addViolation(
                $constraint->message,
                array(
                    '%variant group%' => $variantGroup->getCode(),
                    '%attributes%'    => implode(', ', $notValidAttributes)
                )
            );
        }
    }
}
