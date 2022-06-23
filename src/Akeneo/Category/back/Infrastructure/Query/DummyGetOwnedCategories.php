<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Query;

use Akeneo\Category\Domain\Query\GetOwnedCategories;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DummyGetOwnedCategories implements GetOwnedCategories
{
    public function forUserId(array $categoryCodes, int $userId): array
    {
        return $categoryCodes;
    }
}
