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

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

final class LocaleShouldBeEditableByUser extends Constraint
{
    public string $message = 'channel.validation.upsert.locale_not_editable';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
