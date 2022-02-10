<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Service;

use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

class RandomCodeGeneratorSpec extends ObjectBehavior
{
    public function it_generates_a_random_code(): void
    {
        $code = $this->generate()->getWrappedObject();

        Assert::assertIsString($code);
        Assert::assertMatchesRegularExpression('|[a-zA-Z0-9]{60,120}|', $code);
    }
}
