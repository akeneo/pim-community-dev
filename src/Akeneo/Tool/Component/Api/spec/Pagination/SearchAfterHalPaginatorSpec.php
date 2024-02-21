<?php

namespace spec\Akeneo\Tool\Component\Api\Pagination;

use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\SearchAfterHalPaginator;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class SearchAfterHalPaginatorSpec extends ObjectBehavior
{
    function let(
        RouterInterface $router
    ) {
        $this->beConstructedWith($router);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SearchAfterHalPaginator::class);
    }

    function it_is_a_paginator()
    {
        $this->shouldImplement(PaginatorInterface::class);
    }

    function it_paginates_in_hal_format($normalizer, $router)
    {
        // links
        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'a_text', 'limit'=> 2],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit'=> 2, 'search_after' => null],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'another_text', 'limit'=> 2],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=another_text');

        // embedded
        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA');

        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionB', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB');

        $standardItems = [
            ['code'   => 'optionA'],
            ['code'   => 'optionB'],
        ];

        $expectedItems = [
            '_links'       => [
                'self'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text',
                ],
                'first'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2',
                ],
                'next'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=another_text',
                ],
            ],
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA',
                            ],
                        ],
                        'code'   => 'optionA',
                    ],
                    [
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB',
                            ],
                        ],
                        'code'   => 'optionB',
                    ],
                ],
            ],
        ];

        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after'],
            'search_after'        => ['self' => 'a_text', 'next' => 'another_text'],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];

        $this->paginate($standardItems, $parameters, null)->shouldReturn($expectedItems);
    }

    function it_paginates_in_hal_format_without_using_the_limit_as_query_parameter($router)
    {
        // links
        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'a_text'],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=a_text');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => null],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'another_text'],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=another_text');

        // embedded
        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA');

        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionB', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB');

        $standardItems = [
            ['code'   => 'optionA'],
            ['code'   => 'optionB'],
        ];

        $expectedItems = [
            '_links'       => [
                'self'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=a_text',
                ],
                'first'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after',
                ],
                'next'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=another_text',
                ],
            ],
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA',
                            ],
                        ],
                        'code'   => 'optionA',
                    ],
                    [
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB',
                            ],
                        ],
                        'code'   => 'optionB',
                    ],
                ],
            ],
        ];

        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['pagination_type' => 'search_after'],
            'search_after'        => ['self' => 'a_text', 'next' => 'another_text'],
            'limit'               => 2,
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];

        $this->paginate($standardItems, $parameters, null)->shouldReturn($expectedItems);
    }

    function it_paginates_without_next_link_when_last_page($normalizer, $router)
    {
        // links
        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'a_text', 'limit'=> 2],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit'=> 2, 'search_after' => null],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2');


        // embedded
        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA');

        $standardItems = [
            ['code'   => 'optionA'],
        ];

        $expectedItems = [
            '_links'       => [
                'self'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text',
                ],
                'first'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2',
                ],
            ],
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA',
                            ],
                        ],
                        'code'   => 'optionA',
                    ],
                ],
            ],
        ];

        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after'],
            'search_after'        => ['self' => 'a_text', 'next' => 'another_text'],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];

        $this->paginate($standardItems, $parameters, null)->shouldReturn($expectedItems);
    }

    function it_paginates_with_one_page_when_total_items_equals_zero($normalizer, $router)
    {
        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit'=> 2, 'search_after' => null],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2');

        $expectedItems = [
            '_links'       => [
                'self'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2',
                ],
                'first'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2',
                ],
            ],
            '_embedded'    => [
                'items' => [],
            ],
        ];

        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after'],
            'search_after'        => ['self' => null, 'next' => null],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];

        $this->paginate([], $parameters, null)->shouldReturn($expectedItems);
    }

    function it_throws_an_exception_when_unknown_parameter_given()
    {
        $this->shouldThrow(PaginationParametersException::class)->during('paginate', [[], ['foo' => 'bar'], null]);
    }

    function it_throws_an_exception_when_a_parameter_is_missing()
    {
        $this->shouldThrow(PaginationParametersException::class)->during('paginate', [[], [], null]);
    }

    function it_throws_an_exception_when_no_limit_has_been_defined()
    {
        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['pagination_type' => 'search_after'],
            'search_after'        => ['self' => null, 'next' => null],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];

        $this->shouldThrow(PaginationParametersException::class)->during('paginate', [[], $parameters, null]);
    }
}
