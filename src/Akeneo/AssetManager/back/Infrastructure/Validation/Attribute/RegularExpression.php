<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RegularExpression extends Constraint
{
    public const INVALID_REGULAR_EXPRESSION = 'pim_reference_entity.attribute.validation.regular_expression.invalid_regular_expression';
}
