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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation;

use Symfony\Component\Validator\Constraint;

// TODO remove Constraint suffix like in the other classes
class CleanHTMLTagsOperationConstraint extends Constraint
{
    public function validatedBy(): string
    {
        return CleanHTMLTagsOperationValidator::class;
    }
}
