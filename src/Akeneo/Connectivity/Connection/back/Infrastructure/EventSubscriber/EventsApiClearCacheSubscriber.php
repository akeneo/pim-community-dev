<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EventsApiClearCacheSubscriber implements EventSubscriberInterface
{
    private CacheClearerInterface $cacheClearer;

    public function __construct(CacheClearerInterface $cacheClearer)
    {
        $this->cacheClearer = $cacheClearer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageProcessedEvent::class => 'clearCache',
        ];
    }

    public function clearCache(): void
    {
        $this->cacheClearer->clear();
    }
}
