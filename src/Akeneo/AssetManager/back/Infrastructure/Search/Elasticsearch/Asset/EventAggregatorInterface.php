<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * An event aggregator aggregates some events and dispatches them depending on its internal logic.
 * The `flushEvents` function allows to flush the last events aggregator (before the process terminates for instance).
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
interface EventAggregatorInterface extends EventSubscriberInterface
{
    /**
     * Flush the very last events aggregated)
     */
    public function flushEvents(): void;
}
