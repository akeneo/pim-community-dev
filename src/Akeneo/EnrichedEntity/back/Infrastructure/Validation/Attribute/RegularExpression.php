<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RegularExpression extends Constraint
{
    // TODO: Add the validation on it @see Akeneo\Pim\Structure\Component\Validator\Constraints\ValidRegexValidator
    public const INVALID_REGULAR_EXPRESSION = 'pim_enriched_entity.attribute.validation.regular_expression.invalid_regular_expression';
}
