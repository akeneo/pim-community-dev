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
    public const CHANNEL_SHOULD_EXIST = 'pim_asset_manager.asset_family.validation.rule_template.channel_should_exist';
    public const LOCALE_SHOULD_EXIST = 'pim_asset_manager.asset_family.validation.rule_template.locale_should_exist';
    public const EXTRAPOLATED_ATTRIBUTE_SHOULD_NOT_HAVE_ONE_VALUE_PER_CHANNEL = 'pim_asset_manager.asset_family.validation.rule_template.extrapolated_attribute_type_should_not_have_one_value_per_channel';
    public const EXTRAPOLATED_ATTRIBUTE_SHOULD_NOT_HAVE_ONE_VALUE_PER_LOCALE = 'pim_asset_manager.asset_family.validation.rule_template.extrapolated_attribute_type_should_not_have_one_value_per_locale';
    public const ASSIGNMENT_MODE_NOT_SUPPORTED = 'pim_asset_manager.asset_family.validation.rule_template.assignment_mode_not_supported';
    public const CHANNEL_NOT_SUPPORTED_FOR_FIELD = 'pim_asset_manager.asset_family.validation.rule_template.channel_not_supported_for_field';
    public const LOCALE_NOT_SUPPORTED_FOR_FIELD = 'pim_asset_manager.asset_family.validation.rule_template.locale_not_supported_for_field';
    public const ASSIGNMENT_ATTRIBUTE_DOES_NOT_SUPPORT_THIS_ASSET_FAMILY = 'pim_asset_manager.asset_family.validation.rule_template.assignmement_attribute_does_not_support_this_asset_family';
    public const ASSIGNMENT_ATTRIBUTE_DOES_NOT_EXISTS = 'pim_asset_manager.asset_family.validation.rule_template.assignmement_attribute_does_not_exist';
    public const ASSIGNMENT_ATTRIBUTE_IS_NOT_AN_ASSET_COLLECTION = 'pim_asset_manager.asset_family.validation.rule_template.assignmement_attribute_is_not_an_asset_collection';

    public function validatedBy(): string
    {
        return 'akeneo_assetmanager.validator.asset_family.product_link_rules.rule_engine_validator_acl';
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
