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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\ReferenceEntity;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ReferenceEntityShouldHaveNoRecord extends Constraint
{
    public const ERROR_MESSAGE = 'pim_reference_entity.reference_entity.validation.records.should_have_no_record';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'akeneo_referenceentity.validator.reference_entity.should_have_no_record';
    }
}
