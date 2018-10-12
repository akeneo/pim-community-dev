<?php

namespace Specification\Akeneo\Platform;

use Akeneo\Platform\VersionProviderInterface;
use PhpSpec\ObjectBehavior;

class VersionProviderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(StaticVersion::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VersionProviderInterface::class);
    }

    function it_provides_edition()
    {
        $this->getEdition()->shouldReturn('CE');
    }

    function it_provides_major_version()
    {
        $this->getMajor()->shouldReturn('12');
    }

    function it_provides_minor_version()
    {
        $this->getMinor()->shouldReturn('12.42');
    }

    function it_provides_patch_version()
    {
        $this->getPatch()->shouldReturn('12.42.20');
    }

    function it_provides_stability()
    {
        $this->getStability()->shouldReturn('BETA');
    }

    function it_provides_full_version()
    {
        $this->getFullVersion()->shouldReturn('CE 12.42.20-BETA2 STATIC TEST VERSION');
    }
}

class StaticVersion
{
    /** @staticvar string */
    const VERSION = '12.42.20-BETA2';

    /** @staticvar string */
    const VERSION_CODENAME = 'STATIC TEST VERSION';

    /** @staticvar string */
    const EDITION = 'CE';
}
