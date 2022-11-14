<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Enrichment\Product\back\Infrastructure\AntiCorruptionLayer\ACLGetUserId;
use PhpSpec\ObjectBehavior;

class ACLGetUserIdSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ACLGetUserId::class);
    }


}
