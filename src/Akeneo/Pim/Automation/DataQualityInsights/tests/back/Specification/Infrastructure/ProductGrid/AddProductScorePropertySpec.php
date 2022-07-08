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

class AddProductScorePropertySpec extends ObjectBehavior
{
    public function let(
        AddScoresToProductAndProductModelRows $addScoresToProductAndProductModelRows
    ){
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

        $uuid1 = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $uuid2 = 'ac930366-36f2-4ad9-9a9f-de94c913d8ca';
        $rows = [$this->makeRow($uuid1), $this->makeRow($uuid2)];

        $addScoresToProductAndProductModelRows->__invoke($queryParameters, $rows, 'product')->shouldBeCalled();

        $this->add($queryParameters, $rows)->shouldHaveScoreProperties();
    }

    private function makeRow(string $technicalId): Row
    {
        return Row::fromProduct(
            sprintf('product_%s', $technicalId), // identifier
            null, // family
            [], // groupCodes
            true, // $enabled,
            new \DateTime(), // created
            new \DateTime(), // updated
            sprintf('Label of %s', $technicalId), // label
            null, // image
            null, // completeness,
            $technicalId, //technicalId,
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
