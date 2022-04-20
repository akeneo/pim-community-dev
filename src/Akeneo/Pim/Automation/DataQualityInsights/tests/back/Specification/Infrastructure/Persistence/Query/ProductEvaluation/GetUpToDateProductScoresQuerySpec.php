<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetUpToDateProductScoresQuerySpec extends ObjectBehavior
{
    public function let(
        HasUpToDateEvaluationQueryInterface  $hasUpToDateEvaluationQuery,
        GetProductScoresQueryInterface $getProductScoresQuery
    ) {
        $this->beConstructedWith($hasUpToDateEvaluationQuery, $getProductScoresQuery);
    }

    public function it_returns_the_product_scores_if_the_evaluation_of_the_product_is_up_to_date(
        $hasUpToDateEvaluationQuery,
        $getProductScoresQuery
    ) {
        $productUuid = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');

        $productScores = new Read\Scores(
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(80)),
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(78))
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(67))
        );

        $hasUpToDateEvaluationQuery->forProductId($productUuid)->willReturn(true);
        $getProductScoresQuery->byProductId($productUuid)->willReturn($productScores);

        $this->byProductId($productUuid)->shouldReturn($productScores);
    }

    public function it_returns_empty_scores_if_the_evaluation_of_the_product_is_outdated(
        $hasUpToDateEvaluationQuery,
        $getProductScoresQuery
    ) {
        $productUuid = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');

        $hasUpToDateEvaluationQuery->forProductId($productUuid)->willReturn(false);
        $getProductScoresQuery->byProductId($productUuid)->shouldNotBeCalled();

        $this->byProductId($productUuid)->shouldBeLike(new Read\Scores(new ChannelLocaleRateCollection(), new ChannelLocaleRateCollection()));
    }

    public function it_returns_the_product_scores_only_for_up_to_date_products(
        $hasUpToDateEvaluationQuery,
        $getProductScoresQuery
    ) {
        $productUuidA = new ProductUuid(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'));
        $productUuidB = new ProductUuid(Uuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2'));
        $productUuidC = new ProductUuid(Uuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee'));
        $productUuidCollection = ProductUuidCollection::fromProductUuids([$productUuidA, $productUuidB, $productUuidC]);
        $upToDateProductUuidCollection = ProductUuidCollection::fromProductUuids([$productUuidA, $productUuidB]);
        $productsScores = [
            42 => new Read\Scores(
                (new ChannelLocaleRateCollection())
                    ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100)),
                (new ChannelLocaleRateCollection())
                    ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(90)),
            ),
            123 => new Read\Scores(
                (new ChannelLocaleRateCollection())
                    ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(45)),
                (new ChannelLocaleRateCollection())
                    ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(67)),
            )
        ];

        $hasUpToDateEvaluationQuery->forProductIdCollection($productUuidCollection)->willReturn($upToDateProductUuidCollection);
        $getProductScoresQuery->byProductIds($upToDateProductUuidCollection)->willReturn($productsScores);

        $this->byProductIds($productUuidCollection)->shouldReturn($productsScores);
    }

    public function it_returns_empty_array_if_there_are_no_up_to_date_products(
        $hasUpToDateEvaluationQuery,
        $getProductScoresQuery
    ) {
        $products = ProductUuidCollection::fromInts([42, 123]);

        $hasUpToDateEvaluationQuery->forProductIdCollection($products)->willReturn(null);
        $getProductScoresQuery->byProductIds(Argument::any())->shouldNotBeCalled();

        $this->byProductIds($products)->shouldReturn([]);
    }
}
