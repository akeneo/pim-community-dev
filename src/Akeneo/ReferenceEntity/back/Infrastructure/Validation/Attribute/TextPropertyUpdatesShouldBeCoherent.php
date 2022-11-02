<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class TextPropertyUpdatesShouldBeCoherent extends Constraint
{
    public const SHOULD_BE_A_SIMPLE_TEXT_TO_SET_VALIDATION_RULE_TO_SOMETHING_ELSE_THAN_NONE = 'pim_reference_entity.attribute.validation.should_be_a_simple_text_to_set_validation_rule_to_something_else_than_none';
    public const CANNOT_SET_A_NON_EMPTY_REGULAR_EXPRESSION = 'pim_reference_entity.attribute.validation.cannot_set_a_non_empty_regular_expression';
    public const SHOULD_BE_A_TEXT_AREA_TO_UPDATE_IS_RICH_TEXT_EDITOR = 'pim_reference_entity.attribute.validation.should_be_a_textarea_to_update_is_rich_text_editor';
    public const CANNOT_UPDATE_REGULAR_EXPRESSION = 'pim_reference_entity.attribute.validation.cannot_update_the_regular_expression';
    public const REGULAR_EXPRESSION_SHOULD_NOT_BE_EMPTY =  'pim_reference_entity.attribute.validation.regular_expression.should_not_be_empty';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'akeneo_referenceentity.validator.attribute.text_property_updates_should_be_coherent_validator';
    }
}
