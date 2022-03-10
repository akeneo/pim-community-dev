<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Category\Domain\Query;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DummyGetViewableCategories implements GetViewableCategories
{
    public function forUserId(array $categoryCodes, int $userId): array
    {
        return $categoryCodes;
    }
}
