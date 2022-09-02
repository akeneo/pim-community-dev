<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query\GetAssociatedProductCodesByPublishedProductFromDB;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductAssociation;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Standard\AssociationsNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociationsNormalizerSpec extends ObjectBehavior
{
    function let(GetAssociatedProductCodesByPublishedProductFromDB $getAssociatedProductCodesByPublishedProduct)
    {
        $this->beConstructedWith($getAssociatedProductCodesByPublishedProduct);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldHaveType(AssociationsNormalizer::class);
    }

    function it_has_a_cacheable_supports_method()
    {
        $this->shouldImplement(CacheableSupportsMethodInterface::class);
        $this->hasCacheableSupportsMethod()->shouldBe(true);
    }

    function it_supports_published_product_for_standard_format()
    {
        $publishedProduct = new PublishedProduct();
        $product = new Product();
        $this->supportsNormalization($publishedProduct, 'standard')->shouldReturn(true);
        $this->supportsNormalization($publishedProduct, 'indexing')->shouldReturn(false);
        $this->supportsNormalization($product, 'standard')->shouldReturn(false);
        $this->supportsNormalization($product, 'indexing')->shouldReturn(false);
    }

    function it_normalize_associations_of_a_single_published_product_without_associations()
    {
        $publishedProduct = new PublishedProduct();
        $publishedProduct->setParent(null);

        $this->normalize($publishedProduct)->shouldReturn([]);
    }

    function it_normalizes_associations_of_a_single_published_product_with_associations(
        GetAssociatedProductCodesByPublishedProductFromDB $getAssociatedProductCodesByPublishedProduct
    ) {
        $group1 = new Group();
        $group2 = new Group();
        $group1->setCode('group1');
        $group2->setCode('group2');

        $productModel1 = new ProductModel();
        $productModel2 = new ProductModel();
        $productModel1->setCode('pm1');
        $productModel2->setCode('pm2');

        $originalProduct = new Product();
        $publishedProduct = new PublishedProduct();
        $publishedProduct->setId(10);
        $publishedProduct->setOriginalProduct($originalProduct);

        $associationType = new AssociationType();
        $associationType->setCode('type1');

        $productAssociation = new ProductAssociation();
        $productAssociation->setAssociationType($associationType);
        $productAssociation->setProductModels(new ArrayCollection([$productModel1, $productModel2]));
        $originalProduct->addAssociation($productAssociation);

        $publishedProductAssociation = new PublishedProductAssociation();
        $publishedProductAssociation->setOwner($publishedProduct);
        $publishedProductAssociation->setAssociationType($associationType);
        $publishedProductAssociation->setGroups(new ArrayCollection([$group1, $group2]));
        $publishedProduct->addAssociation($publishedProductAssociation);

        $getAssociatedProductCodesByPublishedProduct->getCodes(10, $publishedProductAssociation)->willReturn(['id1', 'id2']);

        $this->normalize($publishedProduct)->shouldReturn([
            'type1' => [
                'groups' => ['group1', 'group2'],
                'products' => ['id1', 'id2'],
                'product_models' => ['pm1', 'pm2'],
            ],
        ]);
    }

    function it_normalizes_associations_of_a_variant_published_product_with_associations(
        GetAssociatedProductCodesByPublishedProductFromDB $getAssociatedProductCodesByPublishedProduct
    ) {
        $originalProduct = new Product();
        $publishedProduct = new PublishedProduct();
        $publishedProduct->setId(10);
        $publishedProduct->setOriginalProduct($originalProduct);

        $rootProductModel = new ProductModel();
        $publishedProduct->setParent($rootProductModel);

        $associationType1 = new AssociationType();
        $associationType1->setCode('type1');
        $associationType2 = new AssociationType();
        $associationType2->setCode('type2');

        $productModelAssociation1 = new ProductModelAssociation();
        $productModelAssociation1->setAssociationType($associationType1);
        $product3 = new Product();
        $product3->setIdentifier('id3');
        $product4 = new Product();
        $product4->setIdentifier('id4');
        $productModelAssociation1->setProducts(new ArrayCollection([$product3, $product4]));
        $rootProductModel->addAssociation($productModelAssociation1);
        $productModelAssociation2 = new ProductModelAssociation();
        $productModelAssociation2->setAssociationType($associationType2);
        $productModelAssociation2->setProducts(new ArrayCollection([$product3, $product4]));
        $rootProductModel->addAssociation($productModelAssociation2);

        $publishedProductAssociation = new PublishedProductAssociation();
        $publishedProductAssociation->setOwner($publishedProduct);
        $publishedProductAssociation->setAssociationType($associationType1);
        $publishedProduct->addAssociation($publishedProductAssociation);

        $getAssociatedProductCodesByPublishedProduct->getCodes(10, $publishedProductAssociation)->willReturn(['id1', 'id2']);

        $this->normalize($publishedProduct)->shouldReturn([
            'type1' => [
                'groups' => [],
                'products' => ['id3', 'id4', 'id1', 'id2'],
                'product_models' => [],
            ],
            'type2' => [
                'groups' => [],
                'products' => ['id3', 'id4'],
                'product_models' => [],
            ],
        ]);
    }
}
