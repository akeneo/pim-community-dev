<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\ReferenceEntity;

use Symfony\Component\Validator\Constraint;

class ReferenceEntitySelectionConstraint extends Constraint
{
    public const ATTRIBUTE_NOT_FOUND = 'akeneo.tailored_export.validation.refence_entity.attribute_not_found';

    public function validatedBy(): string
    {
        return ReferenceEntitySelectionValidator::class;
    }
}
