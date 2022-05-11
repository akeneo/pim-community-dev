<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Dummy;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DummyTest extends IntegrationTestCase
{
    public function testItRuns(): void
    {
        $this->assertEquals(true, true);
    }
}
