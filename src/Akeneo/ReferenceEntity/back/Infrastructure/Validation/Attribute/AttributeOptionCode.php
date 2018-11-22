<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeOptionCode extends Constraint
{
    public const MESSAGE_WRONG_PATTERN = 'pim_reference_entity.attribute.validation.options.code.pattern';
    public const CODE_SHOULD_NOT_BE_BLANK = 'pim_reference_entity.attribute.validation.options.code.should_not_be_blank';
}
