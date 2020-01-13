<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetFirstVersionIdsByIdsQuery;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\PurgeableVersionList;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\SkipFirstVersionPurgerAdvisor;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurgerAdvisorInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SkipFirstVersionPurgerAdvisorSpec extends ObjectBehavior
{
    function let(SqlGetFirstVersionIdsByIdsQuery $sqlGetFirstVersionIdsByIdsQuery)
    {
        $this->beConstructedWith($sqlGetFirstVersionIdsByIdsQuery);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SkipFirstVersionPurgerAdvisor::class);
    }

    function it_is_a_version_purger_advisor()
    {
        $this->shouldImplement(VersionPurgerAdvisorInterface::class);
    }

    function it_supports_versions_types_only(PurgeableVersionList $versionList)
    {
        $this->supports($versionList)->shouldReturn(true);
    }

    function it_advises_to_not_purge_the_first_version(
        PurgeableVersionList $versionList,
        SqlGetFirstVersionIdsByIdsQuery $sqlGetFirstVersionIdsByIdsQuery
    ) {
        $versionList->getVersionIds()->shouldBeCalled()->willReturn([111, 222]);
        $sqlGetFirstVersionIdsByIdsQuery->execute([111, 222])->shouldBeCalled()->willReturn([111]);
        $versionList->remove([111])->shouldBeCalled();
        $this->isPurgeable($versionList)->shouldReturnAnInstanceOf(PurgeableVersionList::class);
    }
}
