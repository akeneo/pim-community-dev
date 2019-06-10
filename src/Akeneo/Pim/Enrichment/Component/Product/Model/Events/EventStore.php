<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventStore
{
    /** @var ProductEvent[] */
    private $events = [];

    public function add(ProductEvent $event): void
    {
        $this->events[] = $event;
    }

    public function popEvents(string $productIdentifier): array
    {
        $events = $this->events;
        $this->events = [];

        foreach ($events as $event) {
            $event->setProductIdentifier($productIdentifier);
        }

        return $events;
    }
}
