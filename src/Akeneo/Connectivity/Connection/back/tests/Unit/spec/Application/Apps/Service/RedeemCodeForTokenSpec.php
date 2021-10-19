<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Service\RedeemCodeForToken;
use Akeneo\Connectivity\Connection\Application\Apps\Service\RedeemCodeForTokenInterface;
use PhpSpec\ObjectBehavior;

class RedeemCodeForTokenSpec extends ObjectBehavior
{
    public function it_is_a_redeem_code_for_token(): void
    {
        $this->shouldHaveType(RedeemCodeForToken::class);
        $this->shouldImplement(RedeemCodeForTokenInterface::class);
    }
}
