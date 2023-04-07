<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\Fixtures;

use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DummyTest extends TestCase
{
    public function test_it_asserts_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
