<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ShouldBeTextAreaToUpdateIsRichTextEditor extends Constraint
{
    const SHOULD_BE_A_TEXT_AREA_TO_UPDATE_IS_RICH_TEXT_EDITOR = 'pim_enriched_entity.attribute.validation.should_be_a_text_area_to_update_is_rich_text_editor';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_enrichedentity.validator.attribute.should_be_a_text_area_to_update_is_rich_text_editor';
    }
}
