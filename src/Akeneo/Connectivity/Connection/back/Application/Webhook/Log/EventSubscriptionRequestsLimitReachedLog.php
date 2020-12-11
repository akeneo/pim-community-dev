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
    const TYPE = 'event_api.requests_limit';
    const MESSAGE = 'event subscription requests limit has been reached';

    private int $limit;

    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    public static function fromLimit($limit): self
    {
        return new self($limit);
    }

    /**
     * @return array{
     *  type: string,
     *  message: string,
     *  limit: int
     * }
     */
    public function toLog(): array
    {
        return [
            'type' => self::TYPE,
            'message' => self::MESSAGE,
            'limit' => $this->limit,
        ];
    }
}
