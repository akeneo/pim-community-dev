<?php

namespace spec\PimEnterprise\Component\Workflow\Publisher\Product;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\Versioning\Model\Version;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
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
        ProductBuilderInterface $builder,
        PublisherInterface $publisher,
        RelatedAssociationPublisher $associationsPublisher,
        VersionManager $versionManager,
        ProductInterface $product,
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
            $builder,
            $publisher,
            $associationsPublisher,
            $versionManager,
            $productNormalizer,
            $productUpdater
        );
    }

    public function it_publishes_a_product_with_associations(
        $builder,
        $versionManager,
        $product,
        $productNormalizer,
        $productUpdater,
        $publisher,
        $associationsPublisher,
        Version $version,
        AssociationInterface $association,
        AssociationInterface $copiedAssociation,
        PublishedProductInterface $publishedProduct
    ) {
        $versionManager->getNewestLogEntry($product, null)->willReturn($version);

        $product->getIdentifier()->willReturn('sku-01');
        $product->getFamily()->willReturn();
        $product->getAssociations()->willReturn(new ArrayCollection([$association]));

        $productNormalizer->normalize($product, 'standard')->willReturn([]);
        $productUpdater->update($publishedProduct, [])->shouldBeCalled();

        $publishedProduct->setVersion($version)->shouldBeCalled();
        $publishedProduct->setOriginalProduct($product)->shouldBeCalled();
        $publishedProduct->addAssociation($copiedAssociation)->shouldBeCalled();

        $builder->createProduct('sku-01', null)->willReturn($publishedProduct);

        $publisher->publish($association, Argument::cetera())->willReturn($copiedAssociation);
        $associationsPublisher->publish($publishedProduct)->shouldBeCalled();

        $this->publish($product)->shouldReturn($publishedProduct);
    }

    public function it_sets_the_version_during_publishing(
        $builder,
        $versionManager,
        $product,
        $productNormalizer,
        $productUpdater,
        Version $version,
        PublishedProductInterface $publishedProduct
    ) {
        $versionManager->getNewestLogEntry($product, null)->willReturn($version);
        $version->isPending()->willReturn(false);

        $product->getFamily()->willReturn();
        $product->getIdentifier()->willReturn('sku-01');

        $productNormalizer->normalize($product, 'standard')->willReturn([]);
        $productUpdater->update($publishedProduct, [])->shouldBeCalled();

        $builder->createProduct('sku-01', null)->willReturn($publishedProduct);

        $publishedProduct->setVersion($version)->shouldBeCalled();
        $publishedProduct->setOriginalProduct($product)->shouldBeCalled();

        $this->publish($product)->shouldReturn($publishedProduct);
    }

    public function it_builds_the_version_if_needed_during_publishing(
        $builder,
        $versionManager,
        $product,
        $productNormalizer,
        $productUpdater,
        ObjectManager $objectManager,
        Version $pendingVersion,
        Version $newVersion,
        PublishedProductInterface $publishedProduct
    ) {
        $versionManager->getNewestLogEntry($product, null)->willReturn($pendingVersion);
        $pendingVersion->isPending()->willReturn(true);

        $versionManager->buildVersion($product)->willReturn([$pendingVersion, $newVersion]);
        $newVersion->getChangeset()->willReturn(['foo' => 'bar']);
        $pendingVersion->getChangeset()->willReturn([]);

        $versionManager->getObjectManager()->willReturn($objectManager);
        $objectManager->persist($newVersion)->shouldBeCalled();
        $objectManager->persist($pendingVersion)->shouldNotBeCalled();

        $product->getFamily()->willReturn();
        $product->getIdentifier()->willReturn('sku-01');

        $productNormalizer->normalize($product, 'standard')->willReturn([]);
        $builder->createProduct('sku-01', null)->willReturn($publishedProduct);
        $productUpdater->update($publishedProduct, [])->shouldBeCalled();

        $publishedProduct->setVersion($newVersion)->shouldBeCalled();
        $publishedProduct->setOriginalProduct($product)->shouldBeCalled();

        $this->publish($product)->shouldReturn($publishedProduct);
    }
}
