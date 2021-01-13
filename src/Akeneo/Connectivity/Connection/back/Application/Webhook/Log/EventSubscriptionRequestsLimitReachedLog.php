<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventSubscriptionRequestsLimitReachedLog
{
    const TYPE = 'event_api.reach_requests_limit';
    const MESSAGE = 'event subscription requests limit has been reached';

    private int $limit;
    private \DateTimeImmutable $now;
    private int $delayUntilNextRequest;

    private function __construct(int $limit, \DateTimeImmutable $now, int $delayUntilNextRequest)
    {
        $this->limit = $limit;
        $this->now = $now;
        $this->delayUntilNextRequest = $delayUntilNextRequest;
    }

    public static function create(int $limit, \DateTimeImmutable $now, int $delayUntilNextRequest): self
    {
        return new self($limit, $now, $delayUntilNextRequest);
    }

    /**
     * @return array{
     *  type: string,
     *  message: string,
     *  limit: int,
     *  retry_after: int,
     *  limit_reset: string
     * }
     */
    public function toLog(): array
    {
        return [
            'type' => self::TYPE,
            'message' => self::MESSAGE,
            'limit' => $this->limit,
            'retry_after' => $this->delayUntilNextRequest,
            'limit_reset' => $this->now
                ->add(new \DateInterval('PT' . $this->delayUntilNextRequest . 'S'))
                ->format(\DateTimeInterface::ATOM)
        ];
    }
}
