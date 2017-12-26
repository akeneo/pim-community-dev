<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Check if the variant product family is not empty
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotEmptyFamilyValidator extends ConstraintValidator
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

        if (!$constraint instanceof NotEmptyFamily) {
            throw new UnexpectedTypeException($constraint, NotEmptyFamily::class);
        }

        if (null === $product->getFamily()) {
            $this->context->buildViolation(NotEmptyFamily::MESSAGE, [
                   '%sku%' => $product->getIdentifier()
                ])->atPath($constraint->propertyPath)->addViolation();
        }
    }
}
