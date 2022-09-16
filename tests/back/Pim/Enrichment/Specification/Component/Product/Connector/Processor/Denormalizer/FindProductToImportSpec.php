<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductRepository;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\FindProductToImport;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

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

    function it_finds_product_from_flat_data_given_by_the_reader(
        $productRepository,
        ProductInterface $product
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn($product);

        $this->fromFlatData('product_identifier', 'family', null)->shouldReturn($product);
    }

    function it_finds_product_from_uuid_given_by_the_reader(ProductRepository $productRepository)
    {
        $product = new Product();
        $productRepository->findOneByUuid($product->getUuid())->willReturn($product);

        $this->fromFlatData('product_identifier', 'family', $product->getUuid()->toString())->shouldReturn($product);
    }

    function it_creates_product_from_flat_data_given_by_the_reader(
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        ProductInterface $product
    ) {
        $productRepository->findOneByIdentifier('product_identifier')->willReturn(null);
        $productBuilder->createProduct('product_identifier', 'family', null)->willReturn($product);

        $this->fromFlatData('product_identifier', 'family', null)->shouldReturn($product);
    }
}
