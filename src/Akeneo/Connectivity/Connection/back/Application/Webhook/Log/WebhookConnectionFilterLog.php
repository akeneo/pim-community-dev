<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Log;

use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookConnectionFilterLog
{
    const TYPE = 'webhook.skip_event';
    const MESSAGE = 'Current connection owns event: skip.';

    private EventInterface $event;
    private string $connectionCode;

    public function __construct(EventInterface $event, string $connectionCode)
    {
        $this->event = $event;
        $this->connectionCode = $connectionCode;
    }

    /**
     * @return array{
     *  type: string,
     *  message: string,
     *  webhook_connection_code: string,
     *  event: array{
     *      uuid: string,
     *      author: string,
     *      author_type: string,
     *      name: string,
     *      timestamp: int,
     *  }
     * }
     */
    public function toLog(): array
    {
        return [
            'type' => self::TYPE,
            'message' => self::MESSAGE,
            'webhook_connection_code' => $this->connectionCode,
            'event' => [
                'uuid' => $this->event->getUuid(),
                'author' => $this->event->getAuthor()->name(),
                'author_type' => $this->event->getAuthor()->type(),
                'name' => $this->event->getName(),
                'timestamp' => $this->event->getTimestamp(),
            ],
        ];
    }
}
