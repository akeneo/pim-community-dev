<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for variant group axis consraint
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupAxisValidator extends ConstraintValidator
{
    /**
     * Axis must be provided for new variant group
     *
     * @param object     $variantGroup
     * @param Constraint $constraint
     */
    public function validate($variantGroup, Constraint $constraint)
    {
        /** @var GroupInterface */
        if ($variantGroup instanceof GroupInterface) {
            $isNewVariantGroup = $variantGroup->getType()->isVariant() && $variantGroup->getId() === null;
            $hasNoAxis = count($variantGroup->getAxisAttributes()) === 0;
            if ($isNewVariantGroup && $hasNoAxis) {
                $this->context->addViolation(
                    $constraint->message,
                    array(
                        '%variant group%' => $variantGroup->getCode()
                    )
                );
            }
        }
    }
}
