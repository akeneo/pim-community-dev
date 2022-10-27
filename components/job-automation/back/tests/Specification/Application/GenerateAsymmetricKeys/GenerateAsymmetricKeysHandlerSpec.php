<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\JobAutomation\Application\GenerateAsymmetricKeys;

use Akeneo\Platform\JobAutomation\Application\GenerateAsymmetricKeys\GenerateAsymmetricKeysHandler;
use Akeneo\Platform\JobAutomation\Domain\AsymmetricKeysGeneratorInterface;
use Akeneo\Platform\JobAutomation\Domain\Model\AsymmetricKeys;
use Akeneo\Platform\JobAutomation\Domain\Query\SaveAsymmetricKeysQueryInterface;
use PhpSpec\ObjectBehavior;

class GenerateAsymmetricKeysHandlerSpec extends ObjectBehavior
{
    public function let(
        AsymmetricKeysGeneratorInterface $asymmetricKeysGenerator,
        SaveAsymmetricKeysQueryInterface $saveAsymmetricKeysQuery
    ): void {
        $this->beConstructedWith($asymmetricKeysGenerator, $saveAsymmetricKeysQuery);
    }

    public function it_is_instantiable(): void
    {
        $this->beAnInstanceOf(GenerateAsymmetricKeysHandler::class);
    }

    public function it_generates_asymmetric_keys(
        AsymmetricKeysGeneratorInterface $asymmetricKeysGenerator,
        SaveAsymmetricKeysQueryInterface $saveAsymmetricKeysQuery
    ): void {
        $keys = AsymmetricKeys::create('a_public_key', 'a_private_key');
        $asymmetricKeysGenerator->generate()->willReturn($keys);

        $saveAsymmetricKeysQuery->execute($keys)->shouldBeCalled();

        $this->handle();
    }
}
