<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;

/**
 * @author    Pierre-Yves Aillet <pierre-yves.aillet@zenika.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EventsApiDebugResponseErrorLoggerInterface
{
    /**
     * @param array<WebhookEvent> $events
     * @param array<array<string>> $headers
     */
    public function logEventsApiRequestFailed(string $connectionCode, array $events, string $url, int $statusCode, array $headers): void;
}
