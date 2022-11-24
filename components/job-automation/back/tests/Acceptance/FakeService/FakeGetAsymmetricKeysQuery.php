<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\FakeService;

use Akeneo\Platform\JobAutomation\Domain\Model\AsymmetricKeys;
use Akeneo\Platform\JobAutomation\Domain\Query\GetAsymmetricKeysQueryInterface;

final class FakeGetAsymmetricKeysQuery implements GetAsymmetricKeysQueryInterface
{
    public function execute(): AsymmetricKeys
    {
        return AsymmetricKeys::create('a_public_key', 'a_private_key');
    }
}
