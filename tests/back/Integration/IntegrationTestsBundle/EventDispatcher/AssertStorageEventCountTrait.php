<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\EventDispatcher;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait AssertStorageEventCountTrait
{
    public function assertStorageEventCount(int $expectedCount, string $eventName, string $className): void
    {
        /** @var EventDispatcherObserver */
        $eventDispatcher = $this->get('akeneo_integration_tests.event_dispatcher_observer');

        $this->assertSame(
            $expectedCount,
            $eventDispatcher->getStorageEventCount($eventName, $className)
        );
    }
}
