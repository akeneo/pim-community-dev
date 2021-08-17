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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class Sources extends Constraint
{
    public const ASSOCIATION_TYPE_SHOULD_EXIST = 'akeneo.tailored_export.validation.sources.association_type_should_exist';
    public const ATTRIBUTE_SHOULD_EXIST = 'akeneo.tailored_export.validation.attribute.should_exist';

    public function validatedBy(): string
    {
        return 'akeneo.tailored_export.validation.source';
    }
}
