<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithCompletenessesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use PhpSpec\ObjectBehavior;

class GetProductsWithCompletenessesSpec extends ObjectBehavior
{
    public function let(GetProductCompletenesses $getProductCompletenesses): void
    {
        $this->beConstructedWith($getProductCompletenesses);
    }

    public function it_is_a_get_product_with_completenesses(): void
    {
        $this->shouldHaveType(GetProductsWithCompletenesses::class);
        $this->shouldImplement(GetProductsWithCompletenessesInterface::class);
    }

    public function it_builds_a_product_with_completenesses(GetProductCompletenesses $getProductCompletenesses): void
    {
        $completenesses = [
            new ProductCompleteness('ecommerce', 'en_US', 10, 5),
            new ProductCompleteness('ecommerce', 'fr_FR', 10, 1),
            new ProductCompleteness('print', 'en_US', 4, 0),
        ];
        $completenessCollection = new ProductCompletenessCollection(42, $completenesses);
        $connectorProduct = $this->getConnectorProduct(42);
        $getProductCompletenesses->fromProductId(42)->willReturn($completenessCollection);
        $productWithCompleteness = $this->fromConnectorProduct($connectorProduct);

        $productWithCompleteness->completenesses()->shouldReturn($completenessCollection);
    }

    public function it_builds_a_product_list_with_completenesses(GetProductCompletenesses $getProductCompletenesses): void
    {
        $completenesses = [
            new ProductCompleteness('ecommerce', 'en_US', 10, 5),
            new ProductCompleteness('ecommerce', 'fr_FR', 10, 1),
        ];
        $completenessesList = [
            42 => new ProductCompletenessCollection(42, $completenesses),
            13 => new ProductCompletenessCollection(13, $completenesses),
            5 => new ProductCompletenessCollection(5, []),
        ];
        $connectorProductList = new ConnectorProductList(
            2,
            [
                $this->getConnectorProduct(42),
                $this->getConnectorProduct(13),
                $this->getConnectorProduct(5, false),
            ]
        );

        $getProductCompletenesses
            ->fromProductIds([42, 13, 5], 'ecommerce', ['fr_FR', 'en_US'])
            ->willReturn($completenessesList);

        $productListWithCompleteness = $this->fromConnectorProductList($connectorProductList, 'ecommerce', ['fr_FR', 'en_US']);
        foreach ($productListWithCompleteness as $productWithCompleteness) {
            $productWithCompleteness->completeness()->shouldReturn($completenessesList[$productWithCompleteness->id()]);
        }
    }

    private function getConnectorProduct(int $id, bool $withFamily = true): ConnectorProduct
    {
        return new ConnectorProduct(
            $id,
            'identifier',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            $withFamily ? 'clothes' : null,
            [],
            [],
            null,
            [],
            [],
            [],
            new ReadValueCollection(),
            null,
            null
        );
    }
}
