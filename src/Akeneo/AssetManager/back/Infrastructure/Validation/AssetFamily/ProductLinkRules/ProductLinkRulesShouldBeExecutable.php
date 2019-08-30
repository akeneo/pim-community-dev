<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ProductLinkRulesShouldBeExecutable extends Constraint
{
    public const PRODUCT_SELECTION_CANNOT_BE_EMPTY = 'pim_asset_manager.asset_family.validation.rule_template.product_selection_cannot_be_empty';
    public const PRODUCT_ASSIGNMENT_CANNOT_BE_EMPTY = 'pim_asset_manager.asset_family.validation.rule_template.product_assignment_cannot_be_empty';
    public const EXTRAPOLATED_ATTRIBUTE_SHOULD_EXIST = 'pim_asset_manager.asset_family.validation.rule_template.extrapolated_attribute_should_exist';
    public const EXTRAPOLATED_ATTRIBUTE_TYPE_SHOULD_BE_SUPPORTED = 'pim_asset_manager.asset_family.validation.rule_template.extrapolated_attribute_type_should_be_supported';

    public function validatedBy()
    {
        return 'akeneo_assetmanager.validator.asset_family.product_link_rules.rule_engine_validator_acl';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
