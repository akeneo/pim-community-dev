<?php

namespace spec\Pim\Component\Connector\CsvAnalyzer;

use Pim\Component\Connector\Reader\File\CsvReader;
use PhpSpec\ObjectBehavior;

class ProductCsvAnalyzerSpec extends ObjectBehavior
{
    function let(CsvReader $reader)
    {
        $this->beConstructedWith(
            $reader
        );
    }

    function it_analyzes_product_csv($reader)
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

        $reader->setFilePath('my_product_csv_file')->shouldBeCalled();
        $reader->setDelimiter(';')->shouldBeCalled();

        $this->analyzeCsv("my_product_csv_file",";")->shouldBeLike([
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

    function it_analyzes_an_empty_product_csv($reader)
    {
        $reader->read()->willReturn(null);

        $reader->setFilePath('my_product_csv_file')->shouldBeCalled();
        $reader->setDelimiter(';')->shouldBeCalled();

        $this->analyzeCsv("my_product_csv_file",";")->shouldBeLike([
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
