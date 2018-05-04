<?php

namespace spec\Pim\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use PhpSpec\ObjectBehavior;

class SkipFirstVersionPurgerAdvisorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Purger\SkipFirstVersionPurgerAdvisor');
    }

    function it_is_a_version_purger_advisor()
    {
        $this->shouldImplement('Pim\Bundle\VersioningBundle\Purger\VersionPurgerAdvisorInterface');
    }

    function it_supports_versions_types_only(VersionInterface $version, $notAVersionObject)
    {
        $this->supports($version)->shouldReturn(true);
    }

    function it_advises_to_not_purge_the_first_version(VersionInterface $version1)
    {
        $version1->getVersion()->willReturn(1);
        $this->isPurgeable($version1, [])->shouldReturn(false);
    }

    function it_advises_to_purge_other_versions(VersionInterface $version1)
    {
        $version1->getVersion()->willReturn(2);
        $this->isPurgeable($version1, [])->shouldReturn(true);
    }
}
