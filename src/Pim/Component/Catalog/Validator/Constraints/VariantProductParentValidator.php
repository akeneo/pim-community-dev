<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that the parent of a variant product is not a root product model.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class VariantProductParentValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($variantProduct, Constraint $constraint): void
    {
        if (!$variantProduct instanceof VariantProductInterface) {
            throw new UnexpectedTypeException($variantProduct, VariantProductInterface::class);
        }

        if (!$constraint instanceof VariantProductParent) {
            throw new UnexpectedTypeException($constraint, VariantProductParent::class);
        }

        $familyVariant = $variantProduct->getFamilyVariant();
        $parent = $variantProduct->getParent();

        if (null === $familyVariant || null === $parent) {
            return;
        }

        if (!$parent->getProductModels()->isEmpty()) {
            $this->context->buildViolation(VariantProductParent::INVALID_PARENT, [
                '%variant_product%' => $variantProduct->getIdentifier(),
            ])->addViolation();
        }
    }
}
