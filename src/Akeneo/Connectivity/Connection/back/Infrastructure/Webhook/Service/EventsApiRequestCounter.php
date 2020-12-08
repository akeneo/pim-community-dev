<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiRequestCounterInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiRequestCounter implements EventsApiRequestCounterInterface
{
    public function incrementCount(\DateTime $dateTime, int $count): void
    {
        // TODO: Implement incrementCount() method.
    }
}
