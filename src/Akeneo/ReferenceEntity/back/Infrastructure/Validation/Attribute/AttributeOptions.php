<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeOptions extends Constraint
{
    const MESSAGE_TOO_MANY_OPTIONS = 'pim_reference_entity.attribute.validation.attribute_options.too_many';
    const MESSAGE_OPTION_DUPLICATED = 'pim_reference_entity.attribute.validation.attribute_options.duplicated';
}
