<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EventAggregator
{
    /** @var callable[] */
    private $delegates;

    /** @var array */
    private $streams = [];

    public function __construct(callable ...$delegates)
    {
        $this->delegates = $delegates;
    }

    public function addEvents(iterable $events): void
    {
        if ($events instanceof \Generator) {
            $events = iterator_to_array($events);
        }
        if (empty($events)) {
            return;
        }
        $this->streams[] = $events;
    }

    public function dispatch(): void
    {
        foreach ($this->delegates as $delegate) {
            foreach ($this->streams as $events) {
                $delegate($events);
            }
        }
        $this->streams = [];
    }
}
