<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\Analytics\Query\InMemory;

use Akeneo\Tool\Component\Analytics\CountConnectedAppsQueryInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountConnectedAppsQueryInMemory implements CountConnectedAppsQueryInterface
{
    public function execute(): int
    {
        return 2;
    }
}
