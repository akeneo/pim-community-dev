<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid\AddScoresToProductAndProductModelRows;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;

class AddProductModelScorePropertySpec extends ObjectBehavior
{
    public function let(
        AddScoresToProductAndProductModelRows $addScoresToProductAndProductModelRows
    ) {
        $this->beConstructedWith($addScoresToProductAndProductModelRows);
    }

    public function it_returns_row_with_additional_property_DQI_score(
        $addScoresToProductAndProductModelRows,
        ProductQueryBuilderInterface $productQueryBuilder
    )
    {
        $queryParameters = new FetchProductAndProductModelRowsParameters(
            $productQueryBuilder->getWrappedObject(),
            [],
            'ecommerce',
            'en_US'
        );

        $rows = [$this->makeRow(1), $this->makeRow(4)];

        $addScoresToProductAndProductModelRows->__invoke($queryParameters, $rows, 'product_model')->shouldBeCalled();

        $this->add($queryParameters, $rows)->shouldHaveScoreProperties();
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
            $id, //technicalId,
            null, // parentCode,
            new WriteValueCollection() // values,
        );
    }
}
