<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\Manager;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\Builder\VersionBuilder;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class VersionManagerSpec extends ObjectBehavior
{
    function let(
        VersionBuilder $builder,
        ObjectManager $om,
        VersionRepositoryInterface $versionRepository,
        VersionContext $versionContext,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($om, $builder, $versionContext, $eventDispatcher);

        $om->getRepository(Argument::any())->willReturn($versionRepository);
        $versionRepository->findBy(Argument::cetera())->willReturn([]);
        $versionRepository->getNewestLogEntry(Argument::cetera())->willReturn(null);
    }

    function it_is_aware_of_the_versioning_mode()
    {
        $this->isRealTimeVersioning()->shouldReturn(true);
        $this->setRealTimeVersioning(false);
        $this->isRealTimeVersioning()->shouldReturn(false);
    }

    function it_uses_version_builder_to_build_versions($builder, $om, ProductInterface $product)
    {
        $this->setUsername('julia');
        $this->buildVersion($product);

        $builder->buildVersion($product, 'julia', null, null)->shouldHaveBeenCalled();
    }

    function it_builds_versions_for_versionable_entities($om, ProductInterface $product, $builder)
    {
        $builder->buildVersion(Argument::cetera())->willReturn(new Version('foo', 1, 'bar'));

        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(1);
        $versions[0]->shouldBeAnInstanceOf(Version::class);
    }

    function it_creates_pending_versions_when_real_time_versioning_is_disabled(ProductInterface $product, $builder)
    {
        $this->setRealTimeVersioning(false);
        $builder->createPendingVersion(Argument::cetera())->willReturn(new Version('foo', 1, 'bar'));

        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(1);
        $version = $versions[0];
        $version->shouldBeAnInstanceOf(Version::class);
        $version->isPending()->shouldReturn(true);
    }

    function it_builds_pending_versions_and_last_version_when_versioning_an_entity($om, ProductInterface $product, $builder, $versionRepository)
    {
        $product->getId()->willReturn(1);

        $pending1 = new Version('Product', 1, 'julia');
        $pending1->setChangeset(['foo' => 'bar']);
        $pending2 = new Version('Product', 1, 'julia');
        $pending2->setChangeset(['foo' => 'fubar']);
        $versionRepository->findBy(Argument::cetera())->willReturn([$pending1, $pending2]);

        $builder->buildPendingVersion($pending1, null)->willReturn($pending1)->shouldBeCalled();
        $builder->buildPendingVersion($pending2, $pending1)->willReturn($pending2)->shouldBeCalled();
        $builder->buildVersion(Argument::cetera())->willReturn(new Version('Product', 1, 'julia'))->shouldBeCalled();

        $om->detach($pending2)->shouldBeCalled();

        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(3);
    }

    function it_builds_pending_versions_for_a_given_entity(ProductInterface $product, $builder, $versionRepository)
    {
        $product->getId()->willReturn(1);

        $pending1 = new Version('Product', 1, 'julia');
        $pending1->setChangeset(['foo' => 'bar']);
        $pending2 = new Version('Product', 1, 'julia');
        $pending2->setChangeset(['foo' => 'fubar']);
        $versionRepository->findBy(Argument::cetera())->willReturn([$pending1, $pending2]);

        $builder->buildPendingVersion($pending1, null)->willReturn($pending1)->shouldBeCalled();
        $builder->buildPendingVersion($pending2, $pending1)->willReturn($pending2)->shouldBeCalled();

        $versions = $this->buildPendingVersions($product);
        $versions->shouldHaveCount(2);
    }
}
