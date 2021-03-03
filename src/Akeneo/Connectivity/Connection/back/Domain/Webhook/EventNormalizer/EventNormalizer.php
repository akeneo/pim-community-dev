<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer;

use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EventNormalizer implements EventNormalizerInterface
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof EventInterface;
    }

    /**
     * @return array<mixed>
     */
    public function normalize(EventInterface $event): array
    {
        return [
            'action' => $event->getName(),
            'event_id' => $event->getUuid(),
            'event_datetime' => date(\DateTimeInterface::ATOM, $event->getTimestamp()),
            'author' => $event->getAuthor()->name(),
            'author_type' => $event->getAuthor()->type(),
        ];
    }
}
