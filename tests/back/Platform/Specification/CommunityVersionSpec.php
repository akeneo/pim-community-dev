<?php

namespace Specification\Akeneo\Platform;

use Akeneo\Platform\CommunityVersion;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

class CommunityVersionSpec extends ObjectBehavior
{
    /**
     * Do not change it during a pull up.
     * It is useful to hardcode it as master, as it allows to follow who installed CE master thanks to the analytics.
     *
     * Test to remove when tagging a major version.
     */
    function it_is_master_version()
    {
        Assert::assertSame('master', CommunityVersion::VERSION);
        Assert::assertSame('Community master', CommunityVersion::VERSION_CODENAME);
    }
}
