<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid\GetQualityScoresFactory;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AddScoresToProductAndProductModelRowsSpec extends ObjectBehavior
{
    public function let(
        GetQualityScoresFactory         $getQualityScoresFactory,
        ProductEntityIdFactoryInterface $idFactory
    )
    {
        $this->beConstructedWith($getQualityScoresFactory, $idFactory);
    }

    public function it_returns_no_rows_when_given_no_rows(
        ProductQueryBuilderInterface $productQueryBuilder
    )
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
        GetQualityScoresFactory $getQualityScoresFactory,
        ProductEntityIdFactoryInterface $idFactory,
        ProductEntityIdCollection $productIdCollection
    )
    {
        $queryParameters = new FetchProductAndProductModelRowsParameters(
            $productQueryBuilder->getWrappedObject(),
            [],
            'ecommerce',
            'en_US'
        );

        $productUuid1 = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $productUuid2 = 'ac930366-36f2-4ad9-9a9f-de94c913d8ca';
        $productModel1 = 42;
        $productModel2 = 69;
        $rows = [
            $this->makeProductRow($productUuid1),
            $this->makeProductRow($productUuid2),
            $this->makeProductModelRow($productModel1),
            $this->makeProductModelRow($productModel2),
        ];

        $idFactory->createCollection([$productUuid1, $productUuid2])->willReturn($productIdCollection);

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
                                     $getQualityScoresFactory,
                                     $idFactory,
        ProductEntityIdCollection    $productIdCollection
    )
    {
        $queryParameters = new FetchProductAndProductModelRowsParameters(
            $productQueryBuilder->getWrappedObject(),
            [],
            'ecommerce',
            'en_US'
        );

        $productUuid1 = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $productUuid2 = 'ac930366-36f2-4ad9-9a9f-de94c913d8ca';
        $rows = [
            $this->makeProductRow($productUuid1),
            $this->makeProductRow($productUuid2),
            $this->makeProductModelRow(1),
            $this->makeProductModelRow(4),
        ];

        $idFactory->createCollection(['1', '4'])->willReturn($productIdCollection);

        $scores = [
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(96))
                ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(36))
        ];

        $getQualityScoresFactory->__invoke(Argument::any(), 'product_model')->willReturn($scores);

        $this->__invoke($queryParameters, $rows, 'product_model')->shouldHaveScoreProperties();
    }

    private function makeProductRow(string $technicalId): Row
    {
        return Row::fromProduct(
            sprintf('product_or_product_model_%s', $technicalId),
            null,
            [],
            true,
            new \DateTime(),
            new \DateTime(),
            sprintf('Label of %s', $technicalId),
            null,
            null,
            $technicalId,
            null,
            new WriteValueCollection()
        );
    }

    private function makeProductModelRow(int $technicalId): Row
    {
        return Row::fromProductModel(
            sprintf('product_or_product_model_%s', $technicalId),
            'accessories',
            new \DateTime(),
            new \DateTime(),
            sprintf('Label of %s', $technicalId),
            null,
            $technicalId,
            [],
            null,
            new WriteValueCollection()
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
