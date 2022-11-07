<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociations extends Constraint
{
    public const LINK_TYPE_UNEXPECTED_MESSAGE = 'pim_catalog.constraint.quantified_associations.unexpected_link_type';
    public const MAX_ASSOCIATIONS_MESSAGE = 'pim_catalog.constraint.quantified_associations.max_associations';
    public const INVALID_QUANTITY_MESSAGE = 'pim_catalog.constraint.quantified_associations.invalid_quantity';
    public const PRODUCTS_DO_NOT_EXIST_MESSAGE = 'pim_catalog.constraint.quantified_associations.products_do_not_exist';
    public const PRODUCT_MODELS_DO_NOT_EXIST_MESSAGE = 'pim_catalog.constraint.quantified_associations.product_models_do_not_exist';
    public const ASSOCIATION_TYPE_DOES_NOT_EXIST_MESSAGE = 'pim_catalog.constraint.quantified_associations.association_type_does_not_exist';
    public const ASSOCIATION_TYPE_IS_NOT_QUANTIFIED_MESSAGE = 'pim_catalog.constraint.quantified_associations.association_type_is_not_quantified';
    
    public const PRODUCTS_DO_NOT_EXIST_ERROR = '4dc9283b-28d8-4a8f-9e63-b5b86f94a136';
    public const PRODUCT_MODELS_DO_NOT_EXIST_ERROR = '5a563ae0-86d7-46a0-86af-6e4438745fe0';

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
        return 'pim_connector.validator.constraints.quantified_associations_validator';
    }
}
