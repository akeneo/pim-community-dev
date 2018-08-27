<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ValidationRuleShouldBeRegularExpressionToUpdateRegularExpression extends Constraint
{
    public const WRONG_ATTRIBUTE_TYPE_FOR_UPDATE = 'pim_enriched_entity.attribute.validation.regular_expression.wrong_attribute_type_for_update';
    public const WRONG_VALIDATION_RULE_TYPE = 'pim_enriched_entity.attribute.validation.regular_expression.wrong_validation_rule_type';
    public const ATTRIBUTE_SHOULD_BE_A_SIMPLE_TEXT = 'pim_enriched_entity.attribute.validation.regular_expression.attribute_should_be_a_simple_text';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_enrichedentity.validator.attribute.edit_validation_rule_should_be_regular_expression_to_update_regular_expression_validator';
    }
}
