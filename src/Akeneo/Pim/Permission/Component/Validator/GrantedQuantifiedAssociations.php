<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Symfony\Component\Validator\Constraint;

class GrantedQuantifiedAssociations extends Constraint
{
    public const PRODUCTS_DO_NOT_EXIST_ERROR = '4dc9283b-28d8-4a8f-9e63-b5b86f94a136';
    public const PRODUCT_MODELS_DO_NOT_EXIST_ERROR = '5a563ae0-86d7-46a0-86af-6e4438745fe0';
    public const PRODUCTS_DO_NOT_EXIST_MESSAGE = 'pim_catalog.constraint.quantified_associations.products_do_not_exist';
    public const PRODUCT_MODELS_DO_NOT_EXIST_MESSAGE = 'pim_catalog.constraint.quantified_associations.product_models_do_not_exist';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_granted_quantified_associations';
    }
}
