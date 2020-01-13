<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetAllButLastVersionIdsByIdsQuery;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\KeepLastVersionPurgerAdvisor;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\PurgeableVersionList;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurgerAdvisorInterface;
use PhpSpec\ObjectBehavior;

class KeepLastVersionPurgerAdvisorSpec extends ObjectBehavior
{
    function let(SqlGetAllButLastVersionIdsByIdsQuery $sqlGetAllButLastVersionIdsByIdsQuery)
    {
        $this->beConstructedWith($sqlGetAllButLastVersionIdsByIdsQuery);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(KeepLastVersionPurgerAdvisor::class);
    }

    function it_implements_purger_interface()
    {
        $this->shouldImplement(VersionPurgerAdvisorInterface::class);
    }

    function it_supports_versions_types_only(PurgeableVersionList $versionList)
    {
        $this->supports($versionList)->shouldReturn(true);
    }

    function it_advises_to_not_purge_the_last_version(
        PurgeableVersionList $versionList,
        SqlGetAllButLastVersionIdsByIdsQuery $sqlGetAllButLastVersionIdsByIdsQuery
    ) {
        $versionList->getVersionIds()->willReturn([1, 2, 3, 4]);
        $sqlGetAllButLastVersionIdsByIdsQuery->execute([1, 2, 3, 4])->willReturn([1, 2, 3]);
        $versionList->keep([1, 2, 3])->shouldBeCalled()->willReturn($versionList);

        $this->isPurgeable($versionList)->shouldReturn($versionList);
    }
}
