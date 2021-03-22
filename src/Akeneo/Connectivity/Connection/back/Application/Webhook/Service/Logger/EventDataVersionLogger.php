<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger;

use Psr\Log\LoggerInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventDataVersionLogger
{
    const TYPE = 'event_api.event_data_version';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function log(string $version): void
    {
        $log = [
            'type' => self::TYPE,
            'version' => $version
        ];

        $this->logger->info(json_encode($log, JSON_THROW_ON_ERROR));
    }
}
