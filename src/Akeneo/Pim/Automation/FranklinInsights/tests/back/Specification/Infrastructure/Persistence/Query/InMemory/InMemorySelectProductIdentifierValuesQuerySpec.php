<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\InMemory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValuesCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\InMemory\InMemorySelectProductIdentifierValuesQuery;
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

    public function it_returns_an_empty_identifier_values_collection_if_there_is_no_identifiers_mapping(
        $identifiersMappingRepository
    ): void {
        $identifiersMappingRepository->find()->willReturn(new IdentifiersMapping([]));

        $values = $this->execute([42]);
        $values->count()->shouldReturn(0);
    }

    public function it_filters_non_existing_products(
        $productRepository,
        $identifiersMappingRepository,
        AttributeInterface $asin
    ): void {
        $identifiersMapping = new IdentifiersMapping(['asin' => $asin->getWrappedObject()]);

        $identifiersMappingRepository->find()->willReturn($identifiersMapping);
        $productRepository->find(42)->willReturn(null);

        $values = $this->execute([42]);
        $values->shouldBeAnInstanceOf(ProductIdentifierValuesCollection::class);
        $values->count()->shouldReturn(0);
    }

    public function it_returns_mapped_identifier_values_of_products(
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

        $identifiersMapping = new IdentifiersMapping(
            [
                'asin' => $asin->getWrappedObject(),
                'upc' => $ean->getWrappedObject(),
                'mpn' => $mpn->getWrappedObject(),
                'brand' => $brand->getWrappedObject(),
            ]
        );
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);

        $asinValue->hasData()->willReturn(true);
        $asinValue->getData()->willReturn('ABC123');
        $product->getValue('asin')->willReturn($asinValue);

        $product->getValue('ean')->willReturn(null);
        $product->getValue('mpn')->willReturn(null);
        $product->getValue('brand')->willReturn(null);

        $result = $this->execute([42]);
        $result->shouldBeAnInstanceOf(ProductIdentifierValuesCollection::class);

        $values = $result->get(42);
        $values->shouldBeAnInstanceOf(ProductIdentifierValues::class);
        $values->getValue('asin')->shouldReturn('ABC123');
        $values->getValue('upc')->shouldReturn(null);
        $values->getValue('mpn')->shouldReturn(null);
        $values->getValue('brand')->shouldReturn(null);
    }
}
