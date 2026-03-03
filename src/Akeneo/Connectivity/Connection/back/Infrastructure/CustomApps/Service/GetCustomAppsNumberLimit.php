<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Service;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCustomAppsNumberLimit
{
    public function __construct(private int $customAppsNumberLimit)
    {
    }

    public function getLimit(): int
    {
        return $this->customAppsNumberLimit;
    }

    public function setLimit(int $limit): void
    {
        $this->customAppsNumberLimit = $limit;
    }
}
