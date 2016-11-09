<?php

namespace spec\Pim\Bundle\VersioningBundle\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Factory\VersionFactory;
use Akeneo\Component\Versioning\Model\Version;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VersionBuilderSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, VersionFactory $versionFactory)
    {
        $this->beConstructedWith($normalizer, $versionFactory);
    }

    function it_builds_versions_for_versionable_entities($normalizer, $versionFactory, ProductInterface $product, Version $version)
    {
        $product->getId()->willReturn(1);
        $normalizer->normalize($product, 'flat', [])->willReturn(['bar' => 'baz']);
        $versionFactory->create(Argument::Any(), 1, 'foo', null)->willReturn($version);
        $version->setVersion(1)->willReturn($version);
        $version->setSnapshot(['bar' => 'baz'])->willReturn($version);
        $version->setChangeset(['bar' => ['old' => '', 'new' => 'baz']])->willReturn($version);
        $this->buildVersion($product, 'foo');
    }

    function it_creates_pending_version($versionFactory, ProductInterface $product, Version $pending)
    {
        $product->getId()->willReturn(1);
        $versionFactory->create(Argument::Any(), 1, 'baz', null)->willReturn($pending);
        $pending->getChangeset()->willReturn($pending);
        $pending->setChangeset([])->willReturn($pending);
        $pending->getAuthor()->willReturn('baz');
        $pending->isPending()->willReturn(true);

        $version = $this->createPendingVersion($product, 'baz', []);
        $version->shouldBeAnInstanceOf('Akeneo\Component\Versioning\Model\Version');
        $version->getAuthor()->shouldReturn('baz');
        $version->isPending()->shouldReturn(true);
    }

    function it_builds_pending_versions(Version $pending)
    {
        $pending->setVersion(1)->willReturn($pending);
        $pending->setSnapshot(['foo' => 'bar'])->willReturn($pending);
        $pending->getChangeset()->willReturn(['foo' => 'bar']);

        $pending->setChangeset(['foo' => ['old' => '', 'new' => 'bar']])->willReturn($pending);

        $this->buildPendingVersion($pending);
    }
}
