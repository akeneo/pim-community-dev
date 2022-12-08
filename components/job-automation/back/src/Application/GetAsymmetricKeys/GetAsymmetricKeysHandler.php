<?php

namespace Akeneo\Platform\JobAutomation\Application\GetAsymmetricKeys;

use Akeneo\Platform\JobAutomation\Domain\Model\AsymmetricKeys;
use Akeneo\Platform\JobAutomation\Domain\Query\GetAsymmetricKeysQueryInterface;

final class GetAsymmetricKeysHandler implements GetAsymmetricKeysHandlerInterface
{
    public function __construct(
        private readonly GetAsymmetricKeysQueryInterface $getAsymmetricKeysQuery,
    ) {
    }

    public function handle(): AsymmetricKeys
    {
        return $this->getAsymmetricKeysQuery->execute();
    }
}
