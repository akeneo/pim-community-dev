<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\SharedCatalog\Validation;

use Symfony\Component\Validator\Constraint;

class CssColor extends Constraint
{
    public const INVALID_COLOR_MESSAGE = 'shared_catalog.branding.validation.invalid_color';

    public function validatedBy(): string
    {
        return CssColorValidator::class;
    }
}
