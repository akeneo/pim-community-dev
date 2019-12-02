<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Validator\Constraints\Product;

use Symfony\Component\Validator\Constraint;

class ProductCategories extends Constraint
{
    public const ERROR_MESSAGE = 'pim_catalog.constraint.product_cannot_be_classified_in_root_category';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_product_categories_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
