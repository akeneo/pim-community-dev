<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ValidateIdentifiersLimitSpec extends ObjectBehavior
{
    function it_validates_query_of_products_with_search_identifiers_unique_limit_5(): void
    {
        $this->shouldNotThrow(BadRequestException::class)->during('validate', [[
            'identifier' => [
                [
                    'operator' => Operators::IN_LIST,
                    'value' => range(1,5)
                ]
            ]
        ]]);
    }

    function it_validates_query_of_products_with_search_identifiers_unique_limit_100()
    {
        $this->shouldNotThrow(BadRequestException::class)->during('validate', [[
            'identifier' => [
                [
                    'operator' => Operators::IN_LIST,
                    'value' => range(1,100)
                ]
            ]
        ]]);
    }

    function it_throws_exception_when_over_limit_search_identifiers()
    {
        $this->shouldThrow(BadRequestException::class)->during('validate', [[
            'identifier' => [
                [
                    'operator' => Operators::IN_LIST,
                    'value' => range(1,105)
                ]
            ]
        ]]);
    }

    function it_validates_query_of_products_with_search_identifiers_not_unique()
    {
        $this->shouldNotThrow(BadRequestException::class)->during('validate', [[
            'identifier' => [
                [
                    'operator' => Operators::IN_LIST,
                    'value' => array_fill(0,1000, 42)
                ]
            ]
        ]]);
    }

    function it_validates_query_of_products_with_search_identifiers_null()
    {
        $this->shouldThrow(UnprocessableEntityHttpException::class)->during('validate', [[
            'identifier' => [
                [
                    'operator' => Operators::IN_LIST,
                    'value' => null,
                ]
            ]
        ]]);
    }

    function it_validates_query_of_products_with_search_identifiers_not_an_array()
    {
        $this->shouldThrow(UnprocessableEntityHttpException::class)->during('validate', [[
            'identifier' => [
                [
                    'operator' => Operators::IN_LIST,
                    'value' => 'not_an_array',
                ]
            ]
        ]]);
    }
}
