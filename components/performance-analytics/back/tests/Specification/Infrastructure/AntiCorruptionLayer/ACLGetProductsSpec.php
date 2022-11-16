<?php

declare(strict_types=1);

namespace Specification\Akeneo\PerformanceAnalytics\Infrastructure\AntiCorruptionLayer;

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\Product\GetProducts;
use Akeneo\PerformanceAnalytics\Domain\Product\Product;
use Akeneo\PerformanceAnalytics\Infrastructure\AntiCorruptionLayer\ACLGetProducts;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class ACLGetProductsSpec extends ObjectBehavior
{
    public function let(GetConnectorProducts $getConnectorProducts)
    {
        $this->beConstructedWith($getConnectorProducts);
    }

    public function it_is_a_get_products()
    {
        $this->shouldHaveType(ACLGetProducts::class);
        $this->shouldImplement(GetProducts::class);
    }

    public function it_returns_empty_products()
    {
        $this->byUuids([])->shouldReturn([]);
    }

    public function it_returns_a_list_of_products(GetConnectorProducts $getConnectorProducts)
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();

        $creationDate1 = new \DateTimeImmutable('2022-01-01');
        $creationDate2 = new \DateTimeImmutable('2022-02-01');

        $getConnectorProducts->fromProductUuids([$uuid1, $uuid2], Argument::any(), null, null, null)
            ->willReturn(new ConnectorProductList(2, [
                new ConnectorProduct(
                    $uuid1,
                    null,
                    $creationDate1,
                    $creationDate1,
                    true,
                    'family1',
                    ['category1', 'category2'],
                    [],
                    null,
                    [],
                    [],
                    [],
                    new ReadValueCollection([]),
                    new QualityScoreCollection([]),
                    null
                ),
                new ConnectorProduct(
                    $uuid2,
                    null,
                    $creationDate2,
                    $creationDate2,
                    true,
                    null,
                    ['category3'],
                    [],
                    null,
                    [],
                    [],
                    [],
                    new ReadValueCollection([]),
                    new QualityScoreCollection([]),
                    null
                ),
            ]));

        $this->byUuids([$uuid1, $uuid2])->shouldBeLike([
            $uuid1->toString() => Product::fromProperties(
                $uuid1,
                $creationDate1,
                FamilyCode::fromString('family1'),
                [CategoryCode::fromString('category1'), CategoryCode::fromString('category2')]
            ),
            $uuid2->toString() => Product::fromProperties(
                $uuid2,
                $creationDate2,
                null,
                [CategoryCode::fromString('category3')]
            ),
        ]);
    }
}
