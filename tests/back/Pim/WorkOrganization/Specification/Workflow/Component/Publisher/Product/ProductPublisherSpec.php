<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\Product;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Exception\ProductHasNoIdentifierException;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\Product\ProductPublisher;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\Product\RelatedAssociationPublisher;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\PublisherInterface;
use Prophecy\Argument;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductPublisherSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductPublisher::class);
    }

    public function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf(PublisherInterface::class);
    }

    public function let(
        ProductBuilderInterface $builder,
        PublisherInterface $publisher,
        RelatedAssociationPublisher $associationsPublisher,
        VersionManager $versionManager,
        ProductInterface $product,
        NormalizerInterface $productNormalizer,
        ObjectUpdaterInterface $productUpdater,
        PublishedProductRepositoryInterface $publishedProductRepository,
        TranslatorInterface $translator,
    ) {
        $product->getGroups()->willReturn([]);
        $product->getCategories()->willReturn([]);
        $product->getAssociations()->willReturn([]);
        $product->getValues()->willReturn([]);
        $product->getFamily()->willReturn(null);
        $product->isEnabled()->willReturn(true);
        $product->setEnabled(Argument::any())->willReturn($product);

        $this->beConstructedWith(
            $builder,
            $publisher,
            $associationsPublisher,
            $versionManager,
            $productNormalizer,
            $productUpdater,
            $publishedProductRepository,
            $translator
        );
    }

    public function it_publishes_a_product_with_associations(
        ProductBuilderInterface $builder,
        VersionManager $versionManager,
        ProductInterface $product,
        NormalizerInterface $productNormalizer,
        ObjectUpdaterInterface $productUpdater,
        PublisherInterface $publisher,
        RelatedAssociationPublisher $associationsPublisher,
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
        ProductBuilderInterface $builder,
        VersionManager $versionManager,
        ProductInterface $product,
        NormalizerInterface $productNormalizer,
        ObjectUpdaterInterface $productUpdater,
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
        ProductBuilderInterface $builder,
        VersionManager $versionManager,
        ProductInterface $product,
        NormalizerInterface $productNormalizer,
        ObjectUpdaterInterface $productUpdater,
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

    public function it_throw_an_exception_when_publishing_a_product_already_published_by_concurrent_process(
        ProductInterface $product,
        PublishedProductInterface $publishedProduct,
        PublishedProductRepositoryInterface $publishedProductRepository,
        TranslatorInterface $translator
    )
    {
        $product->getIdentifier()->willReturn('sku-01');
        $publishedProductRepository->findOneByOriginalProduct($product)->willReturn($publishedProduct);

        $translator->trans(
            'pimee_enrich.mass_edit.product.operation.publish.already_published_product',
            ['{{ productId }}' => 'sku-01'],
            'jsmessages'
        )->shouldBeCalled()->willReturn('An error occurred while publishing product sku-01 ; Another process is publishing this product.');

        $this->shouldThrow(
            new \LogicException('An error occurred while publishing product sku-01 ; Another process is publishing this product.')
        )->during('publish', [$product]);
    }

    public function it_throws_an_exception_when_publishing_a_product_without_identifier(
        ProductInterface $product,
        UuidInterface $productUuid
    ) {
        $product->getIdentifier()->willReturn(null);
        $product->getUuid()->willReturn($productUuid);

        $this->shouldThrow(
            new ProductHasNoIdentifierException()
        )->during('publish', [$product]);
    }
}
