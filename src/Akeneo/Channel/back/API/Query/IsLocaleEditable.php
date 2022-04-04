<?php

declare(strict_types=1);

namespace Akeneo\Channel\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface IsLocaleEditable
{
    public function forUserId(string $localeCode, int $userId): bool;
}
