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

    function it_provides_full_ce_version()
    {
        $this->beConstructedWith(StaticCommunityVersion::class);
        $this->getFullVersion()->shouldReturn('CE 12.42.20-BETA2 STATIC TEST VERSION');
    }

    function it_tells_if_its_not_a_saas_version()
    {
        $this->beConstructedWith(StaticCommunityVersion::class);
        $this->isSaaSVersion()->shouldReturn(false);
    }

    function it_provides_saas_edition()
    {
        $this->beConstructedWith(StaticSaaSVersion::class);
        $this->getEdition()->shouldReturn('EE SaaS');
    }

    function it_provides_saas_patch()
    {
        $this->beConstructedWith(StaticSaaSVersion::class);
        $this->getPatch()->shouldReturn('20200130151605');
    }

    function it_provides_full_saas_version()
    {
        $this->beConstructedWith(StaticSaaSVersion::class);
        $this->getFullVersion()->shouldReturn('EE SaaS 20200130151605 STATIC TEST VERSION');
    }

    function it_tells_if_its_a_saas_version()
    {
        $this->beConstructedWith(StaticSaaSVersion::class);
        $this->isSaaSVersion()->shouldReturn(true);
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

class StaticSaaSVersion
{
    /** @staticvar string */
    const VERSION = '20200130151605';

    /** @staticvar string */
    const VERSION_CODENAME = 'STATIC TEST VERSION';

    /** @staticvar string */
    const EDITION = 'EE SaaS';
}
