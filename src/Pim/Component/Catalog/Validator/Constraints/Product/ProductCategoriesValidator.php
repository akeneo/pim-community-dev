<?php

namespace Pim\Component\Catalog\Validator\Constraints\Product;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ProductCategoriesValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (! $entity instanceof CategoryAwareInterface) {
            return;
        }

        foreach ($entity->getCategories() as $category) {
            if (null === $category->getParent()) {
                $this->context
                    ->buildViolation(ProductCategories::ERROR_MESSAGE)
                    ->addViolation();

                return;
            }
        }
    }
}
