<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that the variant product has a parent, and that this parent do not
 * have variant products as children.
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
        if (!$variantProduct instanceof ProductInterface) {
            throw new UnexpectedTypeException($variantProduct, ProductInterface::class);
        }

        if (!$constraint instanceof VariantProductParent) {
            throw new UnexpectedTypeException($constraint, VariantProductParent::class);
        }

        if (!$variantProduct->isVariant()) {
            return;
        }

        $parent = $variantProduct->getParent();

        if (null === $parent) {
            $this->context->buildViolation(VariantProductParent::NO_PARENT, [
                '%variant_product%' => $variantProduct->getIdentifier(),
            ])->addViolation();

            return;
        }

        $numberOfLevels = $variantProduct->getFamilyVariant()->getNumberOfLevel();
        $parentLevelAllowed = $numberOfLevels - 1;

        if ($parent->getVariationLevel() !== $parentLevelAllowed) {
            $this->context->buildViolation(VariantProductParent::INVALID_PARENT, [
                '%variant_product%' => $variantProduct->getIdentifier(),
                '%product_model%' => $parent->getCode(),
            ])->atPath($constraint->propertyPath)->addViolation();
        }
    }
}
