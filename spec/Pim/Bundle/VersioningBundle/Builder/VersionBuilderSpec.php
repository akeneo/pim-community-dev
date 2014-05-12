<?php

namespace spec\Pim\Bundle\VersioningBundle\Builder;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;

class VersionBuilderSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_builds_versions_for_versionable_entities($normalizer, AbstractProduct $product)
    {
        $product->getId()->willReturn(1);
        $normalizer->normalize($product, 'csv', ['versioning' => true])->willReturn(['bar' => 'baz']);
        $version = $this->buildVersion($product, 'foo');
        $version->shouldBeAnInstanceOf('Pim\Bundle\VersioningBundle\Entity\Version');
        $version->getAuthor()->shouldReturn('foo');
        $version->isPending()->shouldReturn(false);
        $version->getVersion()->shouldReturn(1);
        $version->getResourceId()->shouldReturn(1);
        $version->getSnapshot()->shouldReturn(['bar' => 'baz']);
        $version->getChangeset()->shouldReturn(['bar' => ['old' => '', 'new' => 'baz']]);
    }

    function it_creates_pending_versions(AbstractProduct $product)
    {
        $version = $this->createPendingVersion($product, 'baz', []);
        $version->shouldBeAnInstanceOf('Pim\Bundle\VersioningBundle\Entity\Version');
        $version->getAuthor()->shouldReturn('baz');
        $version->isPending()->shouldReturn(true);
    }
}
