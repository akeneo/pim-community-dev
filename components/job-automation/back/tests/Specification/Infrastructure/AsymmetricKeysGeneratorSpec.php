<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure;

use Akeneo\Platform\JobAutomation\Domain\Model\AsymmetricKeys;
use Akeneo\Platform\JobAutomation\Infrastructure\AsymmetricKeysGenerator;
use PhpSpec\ObjectBehavior;

class AsymmetricKeysGeneratorSpec extends ObjectBehavior
{
    public function it_is_an_asymmetric_keys_generator(): void
    {
        $this->shouldHaveType(AsymmetricKeysGenerator::class);
    }

    public function it_generates_asymmetric_keys(): void
    {
        $this->generate()->shouldBeAnInstanceOf(AsymmetricKeys::class);
    }
}
