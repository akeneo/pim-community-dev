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

namespace Akeneo\Platform\TailoredImport\Application\GetGroupedTargets;

class GetGroupedTargetsQuery
{
    public const DEFAULT_LIMIT = 25;
    public const DEFAULT_LOCALE = 'en_US';

    public ?string $search = null;
    public int $systemOffset;
    public int $attributeOffset;
    public int $limit = self::DEFAULT_LIMIT;
    public string $locale = self::DEFAULT_LOCALE;
}
