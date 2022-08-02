<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Query;

use Akeneo\Channel\API\Query\IsLocaleReadable;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DummyIsLocaleReadable implements IsLocaleReadable
{
    public function forUserId(string $localeCode, int $userId): bool
    {
        return true;
    }
}
