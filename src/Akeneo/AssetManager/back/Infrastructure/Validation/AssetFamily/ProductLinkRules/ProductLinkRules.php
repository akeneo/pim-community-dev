<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ProductLinkRules extends Constraint
{
    public const PRODUCT_SELECTION_CANNOT_BE_EMPTY = 'pim_asset_manager.asset_family.validation.rule_template.product_selection_cannot_be_empty';
    public const PRODUCT_ASSIGNMENT_CANNOT_BE_EMPTY = 'pim_asset_manager.asset_family.validation.rule_template.product_assignment_to_cannot_be_empty';

    public function validatedBy()
    {
        return 'akeneo_assetmanager.validator.asset_family.product_link_rules.rule_engine_validator_acl';
    }
}
