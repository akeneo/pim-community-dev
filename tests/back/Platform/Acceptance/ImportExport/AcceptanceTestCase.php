<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoTest\Platform\Acceptance\ImportExport;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AcceptanceTestCase extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    protected function get(string $service): ?object
    {
        return self::getContainer()->get($service);
    }
}
