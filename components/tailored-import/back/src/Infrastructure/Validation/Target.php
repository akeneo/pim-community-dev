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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class Target extends Constraint
{
    public const ATTRIBUTE_SHOULD_EXISTS = 'akeneo.tailored_import.validation.target.attribute_should_exists';
    public const PROPERTY_SHOULD_EXISTS = 'akeneo.tailored_import.validation.target.property_should_exists';

    public function validatedBy(): string
    {
        return 'akeneo.tailored_import.validation.target';
    }
}
