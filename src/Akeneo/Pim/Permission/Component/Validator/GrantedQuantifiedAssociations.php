<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Symfony\Component\Validator\Constraint;

class GrantedQuantifiedAssociations extends Constraint
{
    public const PRODUCTS_DO_NOT_EXIST_MESSAGE = 'pim_catalog.constraint.quantified_associations.products_do_not_exist';
    public const PRODUCT_MODELS_DO_NOT_EXIST_MESSAGE = 'pim_catalog.constraint.quantified_associations.product_models_do_not_exist';

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pimee_granted_quantified_associations';
    }
}
