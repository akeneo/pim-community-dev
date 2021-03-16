<?php

namespace Specification\Akeneo\Platform;

use Akeneo\Platform\VersionProviderInterface;
use PhpSpec\ObjectBehavior;

class VersionProviderSpec extends ObjectBehavior
{
    function it_provides_ce_edition()
    {
        $this->beConstructedWith(StaticCommunityVersion::class);
        $this->getEdition()->shouldReturn('CE');
    }

    function it_provides_ce_patch()
    {
        $this->beConstructedWith(StaticCommunityVersion::class);
        $this->getPatch()->shouldReturn('12.42.20');
    }

    function it_provides_ce_minor_version()
    {
        $this->beConstructedWith(StaticCommunityVersion::class);
        $this->getMinorVersion()->shouldReturn('12.42');
    }

    function it_provides_full_ce_version()
    {
        $this->beConstructedWith(StaticCommunityVersion::class);
        $this->getFullVersion()->shouldReturn('CE 12.42.20-BETA2 STATIC TEST VERSION');
    }

    function it_provides_serenity_edition()
    {
        $this->beConstructedWith(StaticSerenityVersion::class);
        $this->getEdition()->shouldReturn('Serenity');
    }

    function it_provides_serenity_patch()
    {
        $this->beConstructedWith(StaticSerenityVersion::class);
        $this->getPatch()->shouldReturn('20200130151605');
    }

    function it_provides_serenity_minor_version()
    {
        $this->beConstructedWith(StaticSerenityVersion::class);
        $this->getMinorVersion()->shouldReturn('20200130151605');
    }

    function it_provides_full_serenity_version()
    {
        $this->beConstructedWith(StaticSerenityVersion::class);
        $this->getFullVersion()->shouldReturn('Serenity 20200130151605 STATIC TEST VERSION');
    }
}

class StaticCommunityVersion
{
    /** @staticvar string */
    const VERSION = '12.42.20-BETA2';

    /** @staticvar string */
    const VERSION_CODENAME = 'STATIC TEST VERSION';

    /** @staticvar string */
    const EDITION = 'CE';
}

class StaticSerenityVersion
{
    /** @staticvar string */
    const VERSION = '20200130151605';

    /** @staticvar string */
    const VERSION_CODENAME = 'STATIC TEST VERSION';

    /** @staticvar string */
    const EDITION = 'Serenity';
}
