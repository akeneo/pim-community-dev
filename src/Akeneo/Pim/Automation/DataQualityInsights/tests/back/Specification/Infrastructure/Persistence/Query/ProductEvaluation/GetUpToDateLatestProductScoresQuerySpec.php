<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetUpToDateLatestProductScoresQuerySpec extends ObjectBehavior
{
    public function let(
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        GetLatestProductScoresQueryInterface $getLatestProductScoresQuery
    ) {
        $this->beConstructedWith($hasUpToDateEvaluationQuery, $getLatestProductScoresQuery);
    }

    public function it_returns_the_latest_product_scores_if_the_evaluation_of_the_product_is_up_to_date(
        $hasUpToDateEvaluationQuery,
        $getLatestProductScoresQuery
    ) {
        $productId = new ProductId(42);

        $productScores = (new ChannelLocaleRateCollection())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(80))
        ;

        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(true);
        $getLatestProductScoresQuery->byProductId($productId)->willReturn($productScores);

        $this->byProductId($productId)->shouldReturn($productScores);
    }

    public function it_returns_empty_scores_if_the_evaluation_of_the_product_is_outdated(
        $hasUpToDateEvaluationQuery,
        $getLatestProductScoresQuery
    ) {
        $productId = new ProductId(42);

        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(false);
        $getLatestProductScoresQuery->byProductId($productId)->shouldNotBeCalled();

        $this->byProductId($productId)->shouldBeLike(new ChannelLocaleRateCollection());
    }

    public function it_returns_the_latest_product_scores_only_for_up_to_date_products(
        $hasUpToDateEvaluationQuery,
        $getLatestProductScoresQuery
    ) {
        $productIdA = new ProductId(42);
        $productIdB = new ProductId(123);
        $productIdC = new ProductId(456);

        $productsScores = [
            42 => (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100)),
            123 => (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(45)),
        ];

        $hasUpToDateEvaluationQuery->forProductIds([$productIdA, $productIdB, $productIdC])->willReturn([$productIdA, $productIdB]);
        $getLatestProductScoresQuery->byProductIds([$productIdA, $productIdB])->willReturn($productsScores);

        $this->byProductIds([$productIdA, $productIdB, $productIdC])->shouldReturn($productsScores);
    }

    public function it_returns_empty_array_if_there_are_no_up_to_date_products(
        $hasUpToDateEvaluationQuery,
        $getLatestProductScoresQuery
    ) {
        $products = [new ProductId(42), new ProductId(123)];

        $hasUpToDateEvaluationQuery->forProductIds($products)->willReturn([]);
        $getLatestProductScoresQuery->byProductIds(Argument::any())->shouldNotBeCalled();

        $this->byProductIds($products)->shouldReturn([]);
    }
}
