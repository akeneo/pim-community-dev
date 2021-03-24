<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Psr\Log\LoggerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventOriginLogger
{
    const TYPE = 'event_api.event_origin';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function log(string $origin): void
    {
        $log = [
            'type' => self::TYPE,
            'origin' => $origin,
        ];

        $this->logger->info(json_encode($log, JSON_THROW_ON_ERROR));
    }
}
