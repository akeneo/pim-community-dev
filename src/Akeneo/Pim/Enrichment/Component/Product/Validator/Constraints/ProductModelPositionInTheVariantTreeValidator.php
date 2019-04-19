<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that, if the family variant has 2 levels (meaning 2 attribute sets),
 * a sub product model can only have a root product model as parent, and if the
 * family variant has 1 level (meaning 1 attribute set), the product model has no
 * parent.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelPositionInTheVariantTreeValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($productModel, Constraint $constraint): void
    {
        if (!$productModel instanceof ProductModelInterface) {
            throw new UnexpectedTypeException($productModel, ProductModelInterface::class);
        }

        if (!$constraint instanceof ProductModelPositionInTheVariantTree) {
            throw new UnexpectedTypeException($constraint, ProductModelPositionInTheVariantTree::class);
        }

        if ($productModel->isRootProductModel()) {
            return;
        }

        $numberOfLevel = $productModel->getFamilyVariant()->getNumberOfLevel();

        if (2 === $numberOfLevel && false === $productModel->getParent()->isRootProductModel()) {
            $this->context->buildViolation(
                ProductModelPositionInTheVariantTree::INVALID_PARENT,
                [
                    '%product_model%' => $productModel->getCode(),
                    '%parent_product_model%' => $productModel->getParent()->getCode(),
                ]
            )->atPath('parent')->addViolation();
        }

        if (1 === $numberOfLevel && null !== $productModel->getParent()) {
            $this->context->buildViolation(
                ProductModelPositionInTheVariantTree::CANNOT_HAVE_PARENT,
                [
                    '%product_model%' => $productModel->getCode(),
                ]
            )->atPath('parent')->addViolation();
        }
    }
}
