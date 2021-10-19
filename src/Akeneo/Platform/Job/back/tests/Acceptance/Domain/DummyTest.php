<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Job\back\tests\Acceptance\Domain;

use Akeneo\Platform\Job\Domain\Dummy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DummyTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    /**
     * @test
     */
    public function it_returns_hello_world()
    {
        $dummy = new Dummy();
        $this->assertEquals('Hello world', $dummy->helloWorld());
    }
}
