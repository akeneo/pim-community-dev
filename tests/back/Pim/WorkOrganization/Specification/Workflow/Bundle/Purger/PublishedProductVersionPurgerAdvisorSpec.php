<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Purger;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query\GetPublishedVersionIdsByVersionIdsQuery;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Purger\PublishedProductVersionPurgerAdvisor;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\PurgeableVersionList;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurgerAdvisorInterface;
use PhpSpec\ObjectBehavior;

class PublishedProductVersionPurgerAdvisorSpec extends ObjectBehavior
{

    function let(GetPublishedVersionIdsByVersionIdsQuery $getPublishedVersionIdsByVersionIdsQuery)
    {
        $this->beConstructedWith($getPublishedVersionIdsByVersionIdsQuery, 'ProductEntityClassName');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PublishedProductVersionPurgerAdvisor::class);
    }

    function it_is_an_advisor()
    {
        $this->shouldImplement(VersionPurgerAdvisorInterface::class);
    }

    function it_supports_products_versions_only()
    {
        $v1 = new PurgeableVersionList('ProductEntityClassName', []);
        $this->supports($v1)->shouldReturn(true);

        $v2 = new PurgeableVersionList('foo', []);
        $this->supports($v2)->shouldReturn(false);
    }

    function it_advises_not_to_purge_published_version(GetPublishedVersionIdsByVersionIdsQuery $getPublishedVersionIdsByVersionIdsQuery)
    {
        $versionIds = [12, 345, 653, 42];
        $getPublishedVersionIdsByVersionIdsQuery->execute($versionIds)->willReturn([345, 42]);

        $purgeableVersionList = $this->isPurgeable(new PurgeableVersionList('ProductEntityClassName', $versionIds));
        $purgeableVersionList->getVersionIds()->shouldBe([12, 653]);
    }
}
