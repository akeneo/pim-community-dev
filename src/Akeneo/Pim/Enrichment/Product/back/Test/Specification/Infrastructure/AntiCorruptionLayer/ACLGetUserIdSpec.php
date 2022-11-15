<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer\ACLGetUserId;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PhpSpec\ObjectBehavior;

class ACLGetUserIdSpec extends ObjectBehavior
{
    function let(UserContext $userContext)
    {
        $this->beConstructedWith($userContext);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ACLGetUserId::class);
    }
}
