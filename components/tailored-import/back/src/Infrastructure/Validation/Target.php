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
    public const ATTRIBUTE_SHOULD_EXIST = 'akeneo.tailored_import.validation.target.attribute_should_exist';
    public const MEASUREMENT_UNIT_SHOULD_EXIST = 'akeneo.tailored_import.validation.target.source_parameter.unit_should_exist';
    public const PROPERTY_SHOULD_EXIST = 'akeneo.tailored_import.validation.target.property_should_exist';

    public function validatedBy(): string
    {
        return 'akeneo.tailored_import.validation.target';
    }
}
