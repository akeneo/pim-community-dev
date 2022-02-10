<?php

namespace Specification\Akeneo\Platform;

use Akeneo\Platform\CommunityVersion;
use Akeneo\Platform\EnterpriseVersion;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

class EnterpriseVersionSpec extends ObjectBehavior
{
    /**
     * Do not change it during a pull up.
     * It is useful to hardcode it as master, as it allows to follow who installed EE master thanks to the analytics.
     *
     * Test to remove when tagging a major version.
     */
    function it_is_master_version()
    {
        Assert::assertSame('master', EnterpriseVersion::VERSION);
    }
}
