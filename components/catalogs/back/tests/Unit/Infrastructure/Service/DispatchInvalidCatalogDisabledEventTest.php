<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Service;

use Akeneo\Catalogs\Infrastructure\Service\DispatchInvalidCatalogDisabledEvent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DispatchInvalidCatalogDisabledEventTest extends TestCase
{
    private ?DispatchInvalidCatalogDisabledEvent $dispatchInvalidCatalogDisabledEvent;

    protected function setUp(): void
    {
        $this->dispatchInvalidCatalogDisabledEvent = self::getContainer()->get(DispatchInvalidCatalogDisabledEvent::class);

        parent::setUp();
    }

    public function testItDispatchesAnInvalidCatalogDisabledEvent(): void
    {
    }
}
