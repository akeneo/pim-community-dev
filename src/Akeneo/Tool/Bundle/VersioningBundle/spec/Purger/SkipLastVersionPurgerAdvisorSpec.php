<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Bundle\VersioningBundle\Purger\SkipLastVersionPurgerAdvisor;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurgerAdvisorInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;

class SkipLastVersionPurgerAdvisorSpec extends ObjectBehavior
{
    function let(VersionRepositoryInterface $versionRepository)
    {
        $this->beConstructedWith($versionRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SkipLastVersionPurgerAdvisor::class);
    }

    function it_implements_purger_interface()
    {
        $this->shouldImplement(VersionPurgerAdvisorInterface::class);
    }

    function it_supports_versions_types_only(VersionInterface $version, $notAVersionObject)
    {
        $this->supports($version)->shouldReturn(true);
    }

    function it_advises_to_not_purge_the_last_version($versionRepository, VersionInterface $version)
    {
        $version->getId()->willReturn(1);
        $version->getResourceName()->willReturn('Product');
        $version->getResourceId()->willReturn(1);

        $versionRepository->getNewestVersionIdForResource('Product', 1)->willReturn(1);

        $this->isPurgeable($version, [])->shouldReturn(false);
    }

    function it_advises_not_to_purge_other_versions($versionRepository, VersionInterface $version)
    {
        $version->getId()->willReturn(2);
        $version->getResourceName()->willReturn('Product');
        $version->getResourceId()->willReturn(1);

        $versionRepository->getNewestVersionIdForResource('Product', 1)->willReturn(3);

        $this->isPurgeable($version, [])->shouldReturn(true);
    }
}
