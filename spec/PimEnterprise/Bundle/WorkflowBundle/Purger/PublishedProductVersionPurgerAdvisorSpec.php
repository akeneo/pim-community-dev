<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Purger\VersionPurgerAdvisorInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Purger\PublishedProductVersionPurgerAdvisor;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Prophecy\Argument;

class PublishedProductVersionPurgerAdvisorSpec extends ObjectBehavior
{

    function let(PublishedProductRepositoryInterface $publishedProductRepository)
    {
        $this->beConstructedWith($publishedProductRepository, 'ProductEntityClassName');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PublishedProductVersionPurgerAdvisor::class);
    }

    function it_is_an_advisor()
    {
        $this->shouldImplement(VersionPurgerAdvisorInterface::class);
    }

    function it_supports_products_versions_only(VersionInterface $v1, VersionInterface $v2)
    {
        $v1->getResourceName()->willReturn('ProductEntityClassName');
        $this->supports($v1)->shouldReturn(true);

        $v2->getResourceName()->willReturn('foo');
        $this->supports($v2)->shouldReturn(false);
    }

    function it_advises_not_to_purge_published_version($publishedProductRepository, VersionInterface $v1)
    {
        $v1->getResourceId()->willReturn(1);
        $v1->getId()->willReturn(1);

        $publishedProductRepository->getPublishedVersionIdByOriginalProductId(1)->willReturn(1);

        $this->isPurgeable($v1, [])->shouldReturn(false);
    }

    function it_advises_to_purge_unpublished_version($publishedProductRepository, VersionInterface $v1)
    {
        $v1->getId()->willReturn(1);
        $v1->getResourceId()->willReturn(1);

        $publishedProductRepository->getPublishedVersionIdByOriginalProductId(1)->willReturn(3);

        $this->isPurgeable($v1, [])->shouldReturn(true);
    }
}
