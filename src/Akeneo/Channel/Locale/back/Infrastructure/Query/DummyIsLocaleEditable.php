<?php

declare(strict_types=1);

namespace Akeneo\Channel\Locale\Infrastructure\Query;

use Akeneo\Channel\Locale\API\Query\IsLocaleEditable;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DummyIsLocaleEditable implements IsLocaleEditable
{
    public function forUserId(string $localeCode, int $userId): bool
    {
        return true;
    }
}
