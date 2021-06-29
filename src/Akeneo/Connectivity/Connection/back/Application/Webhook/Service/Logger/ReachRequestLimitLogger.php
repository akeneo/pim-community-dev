<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Psr\Log\LoggerInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReachRequestLimitLogger
{
    const TYPE = 'event_api.reach_requests_limit';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function log(int $limit, \DateTimeImmutable $reachedLimitDateTime, int $delayUntilNextRequest): void
    {
        $log = [
            'type' => self::TYPE,
            'message' => 'event subscription requests limit has been reached',
            'limit' => $limit,
            'retry_after_seconds' => $delayUntilNextRequest,
            'limit_reset' => $reachedLimitDateTime
                ->add(new \DateInterval('PT' . $delayUntilNextRequest . 'S'))
                ->format(\DateTimeInterface::ATOM)
        ];

        $this->logger->info(json_encode($log, JSON_THROW_ON_ERROR));
    }
}
