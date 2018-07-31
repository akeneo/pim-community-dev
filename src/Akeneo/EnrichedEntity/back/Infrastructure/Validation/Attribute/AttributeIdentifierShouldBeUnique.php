<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeIdentifierShouldBeUnique extends Constraint
{
    public const ERROR_MESSAGE = 'pim_enriched_entity.attribute.validation.identifier.should_be_unique';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_enrichedentity.validator.attribute.identifier_is_unique';
    }
}
