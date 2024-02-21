<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\Analytics\Query\InMemory;

use Akeneo\Tool\Component\Analytics\GetConnectedAppsIdentifiersQueryInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectedAppsIdentifiersQueryInMemory implements GetConnectedAppsIdentifiersQueryInterface
{
    public function execute(): array
    {
        return ['fcde704d-c557-446a-9e7d-c5594eed6801'];
    }
}
