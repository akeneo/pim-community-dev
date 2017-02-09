<?php

namespace spec\PimEnterprise\Component\Workflow\Publisher\Product;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\Versioning\Model\Version;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Publisher\Product\RelatedAssociationPublisher;
use PimEnterprise\Component\Workflow\Publisher\PublisherInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductPublisherSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Workflow\Publisher\Product\ProductPublisher');
    }

    public function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Component\Workflow\Publisher\PublisherInterface');
    }

    public function let(
        PublisherInterface $publisher,
        RelatedAssociationPublisher $associationsPublisher,
        VersionManager $versionManager,
        ProductInterface $product,
        CompletenessManager $completenessManager,
        NormalizerInterface $productNormalizer,
        ObjectUpdaterInterface $productUpdater
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
            'PimEnterprise\Component\Workflow\Model\PublishedProduct',
            $publisher,
            $associationsPublisher,
            $versionManager,
            $completenessManager,
            $productNormalizer,
            $productUpdater
        );
    }

    public function it_publishes_a_product_with_associations(
        $versionManager,
        $product,
        $productNormalizer,
        $productUpdater,
        $publisher,
        $associationsPublisher,
        Version $version,
        AssociationInterface $association,
        AssociationInterface $copiedAssociation
    ) {
        $versionManager->getNewestLogEntry($product, null)->willReturn($version);

        $product->getIdentifier()->willReturn('sku-01');
        $product->getAssociations()->willReturn(new ArrayCollection([$association]));

        $productNormalizer->normalize($product, 'standard')->willReturn([]);
        $productUpdater->update(
            Argument::type('PimEnterprise\Component\Workflow\Model\PublishedProduct'),
            []
        )->shouldBeCalled();

        $publisher->publish($association, Argument::cetera())->willReturn($copiedAssociation);
        $associationsPublisher->publish(Argument::type('PimEnterprise\Component\Workflow\Model\PublishedProduct'))
            ->shouldBeCalled();

        $published = $this->publish($product);
        $published->shouldHaveType('PimEnterprise\Component\Workflow\Model\PublishedProduct');
        $published->getIdentifier()->shouldEqual('sku-01');
        $published->getAssociations()->shouldHaveCount(1);
        $published->getAssociations()[0]->shouldEqual($copiedAssociation);
    }

    public function it_sets_the_version_during_publishing(
        $versionManager,
        $product,
        $productNormalizer,
        $productUpdater,
        Version $version
    ) {
        $versionManager->getNewestLogEntry($product, null)->willReturn($version);
        $version->isPending()->willReturn(false);

        $product->getIdentifier()->willReturn('sku-01');

        $productNormalizer->normalize($product, 'standard')->willReturn([]);
        $productUpdater->update(
            Argument::type('PimEnterprise\Component\Workflow\Model\PublishedProduct'),
            []
        )->shouldBeCalled();

        $published = $this->publish($product);

        $published->getVersion()->shouldReturn($version);
    }

    public function it_builds_the_version_if_needed_during_publishing(
        $versionManager,
        $product,
        $productNormalizer,
        $productUpdater,
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

        $product->getIdentifier()->willReturn('sku-01');

        $productNormalizer->normalize($product, 'standard')
            ->willReturn([]);
        $productUpdater->update(
            Argument::type('PimEnterprise\Component\Workflow\Model\PublishedProduct'),
            []
        )->shouldBeCalled();
        $published = $this->publish($product);

        $published->getVersion()->shouldReturn($pendingVersion);
    }
}
