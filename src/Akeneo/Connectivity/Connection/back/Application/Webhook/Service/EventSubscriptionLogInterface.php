<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service;

use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EventSubscriptionLogInterface
{
    public function logEventDataBuildError(string $message, string $connectionCode, int $userId, object $event): void;
    public function logEventBuild(int $subscriptionCount, int $durationMs, int $eventBuiltCount, object $event): void;
    public function logReachRequestLimit(int $limit, \DateTimeImmutable $reachedLimitDateTime, int $delayUntilNextRequest): void;
    public function logSkipOwnEvent(EventInterface $event, string $connectionCode): void;
}
