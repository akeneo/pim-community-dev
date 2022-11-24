<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\JobAutomation\Application\GetAsymmetricKeys;

use Akeneo\Platform\JobAutomation\Application\GetAsymmetricKeys\GetAsymmetricKeysHandler;
use Akeneo\Platform\JobAutomation\Domain\Query\GetAsymmetricKeysQueryInterface;
use PhpSpec\ObjectBehavior;

class GetAsymmetricKeysHandlerSpec extends ObjectBehavior
{
    public function let(
        GetAsymmetricKeysQueryInterface $getAsymmetricKeysQuery,
    ): void {
        $this->beConstructedWith($getAsymmetricKeysQuery);
    }

    public function it_is_instantiable(): void
    {
        $this->beAnInstanceOf(GetAsymmetricKeysHandler::class);
    }

    public function it_get_asymmetric_keys(
        GetAsymmetricKeysQueryInterface $getAsymmetricKeysQuery,
    ): void {
        $getAsymmetricKeysQuery->execute()->shouldBeCalled();

        $this->handle();
    }
}
