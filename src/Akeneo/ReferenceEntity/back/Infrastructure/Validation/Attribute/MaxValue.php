<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class MaxValue extends Constraint
{
    public const MESSAGE_SHOULD_BE_A_NUMBER = 'pim_reference_entity.attribute.validation.max_value.should_be_a_number';
    public const MESSAGE_MAX_CANNOT_BE_LOWER_THAN_MIN = 'pim_reference_entity.attribute.validation.max_value.cannot_be_lower_than_min';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_referenceentity.validator.attribute.number_attribute.max_value';
    }
}
