<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service;

use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EventSubscriptionSkippedOwnEventLogger
{
    public function logEventSubscriptionSkippedOwnEvent(
        string $connectionCode,
        EventInterface $event
    ): void;
}
