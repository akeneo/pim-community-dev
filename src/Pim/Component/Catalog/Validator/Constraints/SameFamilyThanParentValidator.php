<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\VariantProductInterface;
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
     * @param VariantProductInterface $variantProduct
     */
    public function validate($variantProduct, Constraint $constraint)
    {
        if (!$variantProduct instanceof VariantProductInterface) {
            throw new UnexpectedTypeException($constraint, VariantProductInterface::class);
        }

        if (!$constraint instanceof SameFamilyThanParent) {
            throw new UnexpectedTypeException($constraint, SameFamilyThanParent::class);
        }

        if (null === $parent = $variantProduct->getParent()) {
            return;
        }

        $parentFamily = $parent->getFamilyVariant()->getFamily();

        if (null !== $variantProduct->getFamily() && $variantProduct->getFamily()->getCode() !== $parentFamily->getCode()) {
            $this->context->buildViolation(SameFamilyThanParent::MESSAGE)->atPath($constraint->propertyPath)->addViolation();
        }
    }
}
