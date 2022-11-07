<?php

namespace Akeneo\Platform\JobAutomation\Application\GetAsymmetricKeys;

use Akeneo\Platform\JobAutomation\Domain\Model\AsymmetricKeys;

interface GetAsymmetricKeysHandlerInterface
{
    public function handle(): AsymmetricKeys;
}
