<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductRepository;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\FindProductToImport;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class FindProductToImportSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder
    ) {
        $this->beConstructedWith(
            $productRepository,
            $productBuilder
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FindProductToImport::class);
    }

    function it_finds_product_from_uuid_given_by_the_reader(ProductRepository $productRepository)
    {
        $product = new Product('102376d1-c00d-464c-a189-829f04835c77');
        $productRepository->findOneByUuid($product->getUuid())->willReturn($product);

        $this->fromFlatData('product_identifier', 'family', '102376d1-c00d-464c-a189-829f04835c77')->shouldReturn($product);
    }

    function it_finds_product_from_identifier_if_uuid_is_null(
        $productRepository,
        ProductInterface $product
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn($product);

        $this->fromFlatData('product_identifier', 'family', null)->shouldReturn($product);
    }

    function it_creates_a_new_product_if_provided_uuid_does_not_exist(
        ProductRepository $productRepository,
        ProductBuilderInterface $productBuilder,
        ProductInterface $product
    ) {
        $uuid = Uuid::fromString('102376d1-c00d-464c-a189-829f04835c77');
        $productRepository->findOneByUuid($uuid)->shouldBeCalled()->willReturn(null);
        $productRepository->findOneByIdentifier('product_identifier')->shouldNotBeCalled();

        $productBuilder->createProduct('product_identifier', 'family', '102376d1-c00d-464c-a189-829f04835c77')
            ->shouldBeCalled()->willReturn($product);

        $this->fromFlatData('product_identifier', 'family', '102376d1-c00d-464c-a189-829f04835c77')->shouldReturn($product);
    }

    function it_creates_product_with_identifier(
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        ProductInterface $product
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn(null);
        $productBuilder->createProduct('product_identifier', 'family', null)->willReturn($product);

        $this->fromFlatData('product_identifier', 'family', null)->shouldReturn($product);
    }

    function it_creates_a_product_with_uuid(
        ProductRepository $productRepository,
        ProductBuilderInterface $productBuilder,
        ProductInterface $product
    ) {
        $uuid = Uuid::fromString('102376d1-c00d-464c-a189-829f04835c77');
        $productRepository->findOneByUuid($uuid)->shouldBeCalled()->willReturn(null);

        $productBuilder->createProduct(null, 'family', '102376d1-c00d-464c-a189-829f04835c77')
            ->shouldBeCalled()->willReturn($product);

        $this->fromFlatData(null, 'family', '102376d1-c00d-464c-a189-829f04835c77')->shouldReturn($product);
    }

    function it_creates_a_product_without_identifier_nor_uuid(
        ProductRepository $productRepository,
        ProductBuilderInterface $productBuilder,
        ProductInterface $product
    ) {
        $productRepository->findOneByUuid(Argument::any())->shouldNotBeCalled();
        $productRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $productBuilder->createProduct(null, '', null)
                       ->shouldBeCalled()->willReturn($product);

        $this->fromFlatData(null, '', null)->shouldReturn($product);
    }
}
