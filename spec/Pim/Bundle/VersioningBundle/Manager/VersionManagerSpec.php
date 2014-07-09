<?php

namespace spec\Pim\Bundle\VersioningBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Pim\Bundle\VersioningBundle\Model\Version;

class VersionManagerSpec extends ObjectBehavior
{
    function let(
        SmartManagerRegistry $registry,
        VersionBuilder $builder,
        ObjectManager $om,
        VersionRepositoryInterface $repo
    ) {
        $this->beConstructedWith($registry, $builder);

        $registry->getManagerForClass(Argument::any())->willReturn($om);
        $registry->getRepository(Argument::any())->willReturn($repo);
        $repo->findBy(Argument::cetera())->willReturn([]);
        $repo->getNewestLogEntry(Argument::cetera())->willReturn(null);
    }

    function it_is_aware_of_the_versioning_mode()
    {
        $this->isRealTimeVersioning()->shouldReturn(true);
        $this->setRealTimeVersioning(false);
        $this->isRealTimeVersioning()->shouldReturn(false);
    }

    function it_is_aware_of_the_versioning_context()
    {
        $this->getContext()->shouldReturn(null);
        $this->setContext('import');
        $this->getContext()->shouldReturn('import');
    }

    function it_uses_version_builder_to_build_versions(AbstractProduct $product, $builder)
    {
        $this->setUsername('julia');
        $this->setContext('spec');
        $this->buildVersion($product);

        $builder->buildVersion($product, 'julia', null, 'spec')->shouldHaveBeenCalled();
    }

    function it_builds_versions_for_versionable_entities(AbstractProduct $product, $builder)
    {
        $builder->buildVersion(Argument::cetera())->willReturn(new Version('foo', 1, 'bar'));

        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(1);
        $versions[0]->shouldBeAnInstanceOf('Pim\Bundle\VersioningBundle\Model\Version');
    }

    function it_creates_pending_versions_when_real_time_versioning_is_disabled(AbstractProduct $product, $builder)
    {
        $this->setRealTimeVersioning(false);
        $builder->createPendingVersion(Argument::cetera())->willReturn(new Version('foo', 1, 'bar'));

        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(1);
        $version = $versions[0];
        $version->shouldBeAnInstanceOf('Pim\Bundle\VersioningBundle\Model\Version');
        $version->isPending()->shouldReturn(true);
    }

    function it_builds_pending_versions_when_versioning_an_entity(AbstractProduct $product, $builder, $repo)
    {
        $product->getId()->willReturn(1);

        $pending1 = new Version('Product', 1, 'julia');
        $pending1->setChangeset(['foo' => 'bar']);
        $pending2 = new Version('Product', 1, 'julia');
        $pending2->setChangeset(['foo' => 'fubar']);
        $repo->findBy(Argument::cetera())->willReturn([$pending1, $pending2]);

        $builder->buildPendingVersion($pending1, null)->willReturn($pending1);
        $builder->buildPendingVersion($pending2, $pending1)->willReturn($pending2);
        $builder->buildVersion(Argument::cetera())->willReturn(new Version('Product', 1, 'julia'));

        $versions = $this->buildVersion($product);
        $versions->shouldHaveCount(3);
    }
}
