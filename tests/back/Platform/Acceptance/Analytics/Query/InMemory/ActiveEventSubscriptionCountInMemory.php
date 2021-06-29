<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\Analytics\Query\InMemory;

use Akeneo\Tool\Component\Analytics\ActiveEventSubscriptionCountQuery;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ActiveEventSubscriptionCountInMemory implements ActiveEventSubscriptionCountQuery
{
    public function fetch(): int
    {
        return 666;
    }
}
