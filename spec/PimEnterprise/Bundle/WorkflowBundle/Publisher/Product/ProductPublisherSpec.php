<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Model\Version;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\Product\RelatedAssociationPublisher;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;
use Prophecy\Argument;

class ProductPublisherSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\Publisher\Product\ProductPublisher');
    }

    public function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface');
    }

    public function let(
        PublisherInterface $publisher,
        RelatedAssociationPublisher $associationsPublisher,
        VersionManager $versionManager,
        ProductInterface $product,
        CompletenessManager $completenessManager
    ) {
        $product->getGroups()->willReturn([]);
        $product->getCategories()->willReturn([]);
        $product->getAssociations()->willReturn([]);
        $product->getCompletenesses()->willReturn([]);
        $product->getValues()->willReturn([]);
        $product->getFamily()->willReturn(null);
        $product->getId()->willReturn(1);
        $product->isEnabled()->willReturn(true);
        $product->setEnabled(Argument::any())->willReturn($product);

        $this->beConstructedWith(
            'PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProduct',
            $publisher,
            $associationsPublisher,
            $versionManager,
            $completenessManager
        );
    }

    public function it_publishes_a_product($versionManager, $product, Version $version)
    {
        $versionManager->getNewestLogEntry($product, null)->willReturn($version);

        $published = $this->publish($product);

        $published->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProduct');
    }

    public function it_sets_the_version_during_publishing($versionManager, $product, Version $version)
    {
        $versionManager->getNewestLogEntry($product, null)->willReturn($version);
        $version->isPending()->willReturn(false);

        $published = $this->publish($product);

        $published->getVersion()->shouldReturn($version);
    }

    public function it_copies_enable_during_publishing($versionManager, $product, Version $version)
    {
        $versionManager->getNewestLogEntry($product, null)->willReturn($version);
        $version->isPending()->willReturn(false);

        $enableValues = [true, false];

        foreach ($enableValues as $isEnabled) {
            $product->isEnabled()->willReturn($isEnabled);
            $published = $this->publish($product);
            $published->isEnabled()->shouldReturn($isEnabled);
        }
    }

    public function it_builds_the_version_if_needed_during_publishing(
        $versionManager,
        $product,
        ObjectManager $objectManager,
        Version $pendingVersion,
        Version $newVersion
    ) {
        $versionManager->getNewestLogEntry($product, null)->willReturn($pendingVersion);
        $pendingVersion->isPending()->willReturn(true);

        $versionManager->buildVersion($product)->willReturn([$pendingVersion, $newVersion]);
        $pendingVersion->getChangeset()->willReturn(['foo' => 'bar']);
        $newVersion->getChangeset()->willReturn([]);

        $versionManager->getObjectManager()->willReturn($objectManager);
        $objectManager->persist($pendingVersion)->shouldBeCalled();
        $objectManager->persist($newVersion)->shouldNotBeCalled();

        $published = $this->publish($product);

        $published->getVersion()->shouldReturn($pendingVersion);
    }
}
