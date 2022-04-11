<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid\GetQualityScoresFactory;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class AddScoresToProductAndProductModelRowsSpec extends ObjectBehavior
{
    public function let(
        GetQualityScoresFactory $getQualityScoresFactory
    ){
        $this->beConstructedWith($getQualityScoresFactory);
    }

    public function it_returns_no_rows_when_given_no_rows(ProductQueryBuilderInterface $productQueryBuilder)
    {
        $queryParameters = new FetchProductAndProductModelRowsParameters(
            $productQueryBuilder->getWrappedObject(),
            [],
            'ecommerce',
            'en_US'
        );

        $this->__invoke($queryParameters, [], '')->shouldReturn([]);
    }

    public function it_returns_product_row_with_additional_property_DQI_score(
        ProductQueryBuilderInterface $productQueryBuilder,
        $getQualityScoresFactory
    ){
        $queryParameters = new FetchProductAndProductModelRowsParameters(
            $productQueryBuilder->getWrappedObject(),
            [],
            'ecommerce',
            'en_US'
        );

        $rows = [$this->makeRow(1), $this->makeRow(4)];

        $scores = [
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(96))
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(36))
        ];

        $getQualityScoresFactory->__invoke(Argument::any(), 'product')->willReturn($scores);

        $this->__invoke($queryParameters, $rows, 'product')->shouldHaveScoreProperties();
    }

    public function it_returns_product_model_row_with_additional_property_DQI_score(
        ProductQueryBuilderInterface $productQueryBuilder,
                                     $getQualityScoresFactory
    ){
        $queryParameters = new FetchProductAndProductModelRowsParameters(
            $productQueryBuilder->getWrappedObject(),
            [],
            'ecommerce',
            'en_US'
        );

        $rows = [$this->makeRow(1), $this->makeRow(4)];

        $scores = [
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(96))
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(36))
        ];

        $getQualityScoresFactory->__invoke(Argument::any(), 'product_model')->willReturn($scores);

        $this->__invoke($queryParameters, $rows, 'product_model')->shouldHaveScoreProperties();
    }

    private function makeRow(int $id): Row
    {
        return Row::fromProduct(
            strval($id), // identifier
            null, // family
            [], // groupCodes
            true, // $enabled,
            new \DateTime(), // created
            new \DateTime(), // updated
            strval($id), // label
            null, // image
            null, // completeness,
            Uuid::uuid4()->toString(), //technicalId,
            null, // parentCode,
            new WriteValueCollection() // values,
        );
    }

    public function getMatchers(): array
    {
        return [
            'haveScoreProperties' => function (array $rows) {
                foreach ($rows as $index => $row) {
                    $properties = iterator_to_array($row->additionalProperties()->getIterator());
                    $values = array_filter($properties, function ($property) {
                        return $property->name() === 'data_quality_insights_score';
                    });
                    if (count($values) === 0) {
                        throw new FailureException("Property not found for Row at index " . $index);
                    }
                }
                return true;
            }
        ];
    }
}
