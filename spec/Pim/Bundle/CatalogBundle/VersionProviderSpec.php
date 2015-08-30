<?php

namespace spec\Pim\Bundle\CatalogBundle;

use PhpSpec\ObjectBehavior;

class VersionProviderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('spec\Pim\Bundle\CatalogBundle\StaticVersion');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\VersionProviderInterface');
    }

    function it_provides_edition()
    {
        $this->getEdition()->shouldReturn('CE');
    }

    function it_provides_major_version()
    {
        $this->getMajor()->shouldReturn('1');
    }

    function it_provides_minor_version()
    {
        $this->getMinor()->shouldReturn('1.4');
    }

    function it_provides_patch_version()
    {
        $this->getPatch()->shouldReturn('1.4.0');
    }

    function it_provides_stability()
    {
        $this->getStability()->shouldReturn('BETA');
    }
}

class StaticVersion
{
    /** @staticvar string */
    const VERSION = '1.4.0-BETA2';

    /** @staticvar string */
    const VERSION_CODENAME = 'STATIC TEST VERSION';

    /** @staticvar string */
    const EDITION = 'CE';
}
