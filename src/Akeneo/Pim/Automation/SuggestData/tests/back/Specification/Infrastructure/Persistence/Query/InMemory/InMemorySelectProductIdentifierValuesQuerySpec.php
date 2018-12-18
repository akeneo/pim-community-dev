<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\InMemory;

use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\InMemory\InMemorySelectProductIdentifierValuesQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class InMemorySelectProductIdentifierValuesQuerySpec extends ObjectBehavior
{
    public function let(
        ProductRepositoryInterface $productRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ): void {
        $this->beConstructedWith($productRepository, $identifiersMappingRepository);
    }

    public function it_is_a_select_product_identifier_values_query(): void
    {
        $this->shouldImplement(SelectProductIdentifierValuesQueryInterface::class);
    }

    public function it_is_an_inmemory_implementation_of_a_select_product_identifier_values_query(): void
    {
        $this->shouldBeAnInstanceOf(InMemorySelectProductIdentifierValuesQuery::class);
    }

    public function it_returns_null_if_there_is_no_identifiers_mapping(
        $productRepository,
        $identifiersMappingRepository
    ): void {
        $productRepository->find(42)->willReturn(new Product());
        $identifiersMappingRepository->find()->willReturn(new IdentifiersMapping([]));

        $this->execute(42)->shouldReturn(null);
    }

    public function it_returns_null_if_the_product_does_not_exist(
        $productRepository,
        $identifiersMappingRepository,
        AttributeInterface $asin
    ): void {
        $identifiersMappingRepository->find()->willReturn(
            new IdentifiersMapping(
                [
                    'asin' => $asin->getWrappedObject(),
                ]
            )
        );
        $productRepository->find(42)->willReturn(null);

        $this->execute(42)->shouldReturn(null);
    }

    public function it_returns_mapped_identifier_values_of_a_product(
        $productRepository,
        $identifiersMappingRepository,
        ProductInterface $product,
        AttributeInterface $asin,
        AttributeInterface $ean,
        AttributeInterface $mpn,
        AttributeInterface $brand,
        ValueInterface $asinValue
    ): void {
        $productRepository->find(42)->willReturn($product);

        $asin->getCode()->willReturn('asin');
        $ean->getCode()->willReturn('ean');
        $mpn->getCode()->willReturn('mpn');
        $brand->getCode()->willReturn('brand');

        $identifiersMappingRepository->find()->willReturn(
            new IdentifiersMapping(
                [
                    'asin' => $asin->getWrappedObject(),
                    'upc' => $ean->getWrappedObject(),
                    'mpn' => $mpn->getWrappedObject(),
                    'brand' => $brand->getWrappedObject(),
                ]
            )
        );

        $asinValue->hasData()->willReturn(true);
        $asinValue->getData()->willReturn('ABC123');
        $product->getValue('asin')->willReturn($asinValue);

        $product->getValue('ean')->willReturn(null);
        $product->getValue('mpn')->willReturn(null);
        $product->getValue('brand')->willReturn(null);

        $result = $this->execute(42);
        $result->shouldBeAnInstanceOf(ProductIdentifierValues::class);
        $result->identifierValues()->shouldBeLike(
            [
                'asin' => 'ABC123',
                'upc' => null,
                'mpn' => null,
                'brand' => null,
            ]
        );
    }
}
