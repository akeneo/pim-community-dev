<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration;

use Akeneo\Catalogs\Test\Integration\Fakes\TimestampableSubscriber;
use Kernel as AppKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class Kernel extends AppKernel
{
    protected function build(ContainerBuilder $container): void
    {
        $pimCatalogTimestampableSubscriber = new Definition(TimestampableSubscriber::class, []);
        $pimCatalogTimestampableSubscriber->setDecoratedService('pim_catalog.event_subscriber.timestampable');

        $pimVersioningTimestampableSubscriber = new Definition(TimestampableSubscriber::class, []);
        $pimVersioningTimestampableSubscriber->setDecoratedService('pim_versioning.event_subscriber.timestampable');

        $container->addDefinitions([
            'test.pim_catalog.event_subscriber.timestampable' => $pimCatalogTimestampableSubscriber,
            'test.pim_versioning.event_subscriber.timestampable' => $pimVersioningTimestampableSubscriber,
        ]);
    }
}
