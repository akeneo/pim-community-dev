<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

interface RedeemCodeForTokenInterface
{
    public function redeem(string $code): string;
}
