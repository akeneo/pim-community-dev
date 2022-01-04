<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\back\tests\Integration\Domain;

use Akeneo\Platform\TailoredImport\Domain\Dummy;
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
