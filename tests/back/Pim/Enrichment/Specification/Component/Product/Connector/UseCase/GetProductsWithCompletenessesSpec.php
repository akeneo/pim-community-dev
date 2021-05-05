<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
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
        $connectorProduct = new ConnectorProduct(
            42,
            'identifier',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'clothes',
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
        $getProductCompletenesses->fromProductId(42)->willReturn($completenessCollection);
        $productWithCompleteness = $this->fromConnectorProduct($connectorProduct);

        $productWithCompleteness->completenesses()->shouldReturn($completenessCollection);
    }
}
