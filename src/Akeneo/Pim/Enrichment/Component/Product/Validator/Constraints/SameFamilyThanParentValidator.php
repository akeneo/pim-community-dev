<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Check if the variant product family is the same than its parents
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SameFamilyThanParentValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     *
     * @param ProductInterface $product
     */
    public function validate($product, Constraint $constraint)
    {
        if (!$product instanceof ProductInterface) {
            throw new UnexpectedTypeException($constraint, ProductInterface::class);
        }

        if (!$constraint instanceof SameFamilyThanParent) {
            throw new UnexpectedTypeException($constraint, SameFamilyThanParent::class);
        }

        if (!$product->isVariant()) {
            return;
        }

        if (null === $parent = $product->getParent()) {
            return;
        }

        $parentFamily = $parent->getFamilyVariant()->getFamily();

        if (null !== $product->getFamily() && $product->getFamily()->getCode() !== $parentFamily->getCode()) {
            $this->context->buildViolation(SameFamilyThanParent::MESSAGE)->atPath($constraint->propertyPath)->addViolation();
        }
    }
}
