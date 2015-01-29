<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for variant group axis constraint
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
            $isNew = $variantGroup->getId() === null;
            $isVariantGroup = $variantGroup->getType()->isVariant();
            $hasAxis = count($variantGroup->getAxisAttributes()) > 0;
            if ($isNew && $isVariantGroup && !$hasAxis) {
                $this->context->addViolation(
                    $constraint->expectedAxisMessage,
                    array(
                        '%variant group%' => $variantGroup->getCode()
                    )
                );
            } elseif (!$isVariantGroup && $hasAxis) {
                $this->context->addViolation(
                    $constraint->unexpectedAxisMessage,
                    array(
                        '%group%' => $variantGroup->getCode()
                    )
                );
            }
        }
    }
}
