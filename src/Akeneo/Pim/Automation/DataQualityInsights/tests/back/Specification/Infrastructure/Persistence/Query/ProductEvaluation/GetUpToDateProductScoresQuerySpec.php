<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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
        $productId = new ProductId(42);

        $productScores = new Read\Scores(
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(80)),
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(78))
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(67))
        );

        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(true);
        $getProductScoresQuery->byProductId($productId)->willReturn($productScores);

        $this->byProductId($productId)->shouldReturn($productScores);
    }

    public function it_returns_empty_scores_if_the_evaluation_of_the_product_is_outdated(
        $hasUpToDateEvaluationQuery,
        $getProductScoresQuery
    ) {
        $productId = new ProductId(42);

        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(false);
        $getProductScoresQuery->byProductId($productId)->shouldNotBeCalled();

        $this->byProductId($productId)->shouldBeLike(new Read\Scores(new ChannelLocaleRateCollection(), new ChannelLocaleRateCollection()));
    }

    public function it_returns_the_product_scores_only_for_up_to_date_products(
        $hasUpToDateEvaluationQuery,
        $getProductScoresQuery
    ) {
        $productIdA = new ProductId(42);
        $productIdB = new ProductId(123);
        $productIdC = new ProductId(456);
        $productIdCollection = ProductIdCollection::fromProductIds([$productIdA, $productIdB, $productIdC]);
        $upToDateProductIdCollection = ProductIdCollection::fromProductIds([$productIdA, $productIdB]);
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

        $hasUpToDateEvaluationQuery->forProductIdCollection($productIdCollection)->willReturn($upToDateProductIdCollection);
        $getProductScoresQuery->byProductIds($upToDateProductIdCollection)->willReturn($productsScores);

        $this->byProductIds($productIdCollection)->shouldReturn($productsScores);
    }

    public function it_returns_empty_array_if_there_are_no_up_to_date_products(
        $hasUpToDateEvaluationQuery,
        $getProductScoresQuery
    ) {
        $products = ProductIdCollection::fromInts([42, 123]);

        $hasUpToDateEvaluationQuery->forProductIdCollection($products)->willReturn(null);
        $getProductScoresQuery->byProductIds(Argument::any())->shouldNotBeCalled();

        $this->byProductIds($products)->shouldReturn([]);
    }
}
