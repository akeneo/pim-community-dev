<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use PhpSpec\ObjectBehavior;

class ProductAnalyzerSpec extends ObjectBehavior
{
    function it_analyzes_product_data(ItemReaderInterface $reader)
    {
        $data = [
            [ "s01","my_family1","Tot", "Nice prod"],
            [ "s02","my_family2","",    "Another prod"],
            [ "s03","my_family2","",    ""],
            [ "s04","my_family1","Prod",""]
        ];

        $reader->read()->will(
            function () use (&$data) {
                $line = current($data);
                next($data);

                return $line;
            }
        );

        $this->analyze($reader)->shouldBeLike([
            "columns_count" => 4,
            "products" => [
                "count" => 4,
                "values_count" => 12,
                "values_per_product" => [
                    "min" => [
                        "count" => 2,
                        "line_number" => 3
                    ],
                    "max" => [
                        "count" => 4,
                        "line_number" => 1
                    ],
                    "average" => 3
                 ]
             ]
        ]);
    }

    function it_analyzes_empty_product_data(ItemReaderInterface $reader)
    {
        $reader->read()->willReturn(null);

        $this->analyze($reader)->shouldBeLike([
            "columns_count" => 0,
            "products" => [
                "count" => 0,
                "values_count" => 0,
                "values_per_product" => [
                ]
            ]
        ]);
    }
}
