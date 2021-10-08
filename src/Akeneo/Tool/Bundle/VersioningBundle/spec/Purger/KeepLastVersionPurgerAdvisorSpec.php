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

    function it_supports_versions_types_only()
    {
        $versionList = new PurgeableVersionList('resource_name', [111, 666]);
        $this->supports($versionList)->shouldReturn(true);
    }

    function it_advises_to_not_purge_the_last_version(
        SqlGetAllButLastVersionIdsByIdsQuery $sqlGetAllButLastVersionIdsByIdsQuery
    ) {
        $versionList = new PurgeableVersionList('resource_name', [1, 2, 3, 4]);
        $sqlGetAllButLastVersionIdsByIdsQuery->execute([1, 2, 3, 4])->willReturn([1, 2, 3]);

        $this->isPurgeable($versionList)->shouldBeLike(new PurgeableVersionList('resource_name', [1, 2, 3]));
    }

    function it_returns_no_version_when_all_are_last_version(
        SqlGetAllButLastVersionIdsByIdsQuery $sqlGetAllButLastVersionIdsByIdsQuery
    ) {
        $versionList = new PurgeableVersionList('resource_name', [1, 2, 3, 4]);
        $sqlGetAllButLastVersionIdsByIdsQuery->execute([1, 2, 3, 4])->willReturn([]);

        $this->isPurgeable($versionList)->shouldBeLike(new PurgeableVersionList('resource_name', []));
    }
}
