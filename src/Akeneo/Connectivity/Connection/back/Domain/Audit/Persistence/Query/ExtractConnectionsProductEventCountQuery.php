<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ExtractConnectionsProductEventCountQuery
{
    /**
     * @return HourlyEventCount[]
     */
    public function extractCreatedProductsByConnection(HourlyInterval $hourlyInterval): array;

    /**
     * @return HourlyEventCount[]
     */
    public function extractAllCreatedProducts(HourlyInterval $hourlyInterval): array;

    /**
     * @return HourlyEventCount[]
     */
    public function extractUpdatedProductsByConnection(HourlyInterval $hourlyInterval): array;

    /**
     * @return HourlyEventCount[]
     */
    public function extractAllUpdatedProducts(HourlyInterval $hourlyInterval): array;
}
