<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\EventAggregatorInterface;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EventAggregatorStub implements EventAggregatorInterface
{
    private bool $isFlushed = false;
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function flushEvents(): void
    {
        $this->isFlushed = true;
    }

    public function assertAssetEventsFlushed()
    {
        Assert::assertTrue($this->isFlushed, 'The event aggregator has not been flushed');
    }
}
