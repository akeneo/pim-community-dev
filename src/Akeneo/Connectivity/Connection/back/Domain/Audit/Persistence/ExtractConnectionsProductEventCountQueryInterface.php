<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ExtractConnectionsProductEventCountQueryInterface
{
    /**
     * @return HourlyEventCount[]
     */
    public function extractCreatedProductsByConnection(HourlyInterval $hourlyInterval): array;

    /**
     * @return HourlyEventCount[]
     */
    public function extractUpdatedProductsByConnection(HourlyInterval $hourlyInterval): array;
}
