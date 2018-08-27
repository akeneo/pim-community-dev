<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ShouldBeASimpleTextToUpdateValidationRule extends Constraint
{
    const SHOULD_BE_A_SIMPLE_TEXT_TO_UPDATE_VALIDATION_RULE = 'pim_enriched_entity.attribute.validation.should_be_a_simple_text_to_update_validation_rule';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_enrichedentity.validator.attribute.should_be_a_simple_text_to_update_validation_rule';
    }
}
