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

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Source\AssetCollection;

use Symfony\Component\Validator\Constraint;

class AssetCollectionSourceConstraint extends Constraint
{
    public function validatedBy(): string
    {
        return AssetCollectionSourceValidator::class;
    }
}
