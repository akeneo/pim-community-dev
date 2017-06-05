<?php

namespace spec\Pim\Component\Api\Pagination;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Exception\PaginationParametersException;
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
        $this->shouldHaveType('Pim\Component\Api\Pagination\SearchAfterHalPaginator');
    }

    function it_is_a_paginator()
    {
        $this->shouldImplement('Pim\Component\Api\Pagination\PaginatorInterface');
    }

    function it_paginates_in_hal_format($router)
    {
        // links
        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => null, 'search_before' => null, 'limit'=> 2],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit'=> 2, 'search_after' => null, 'search_before' => null],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'optionB', 'limit'=> 2, 'search_before' => null],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=optionB');

        // embedded
        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA', 'search_after' => null, 'search_before' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA');

        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionB', 'search_after' => null, 'search_before' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB');

        $standardItems = [
            ['code'   => 'optionA'],
            ['code'   => 'optionB'],
        ];

        $expectedItems = [
            '_links'       => [
                'self'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2',
                ],
                'first'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2',
                ],
                'next'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=optionB',
                ],
            ],
            'current_page' => null,
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
            'search_after'        => ['self' => '', 'next' => 'optionB', 'previous' => ''],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];

        $this->paginate($standardItems, $parameters, null)->shouldReturn($expectedItems);
    }

    function it_paginates_with_previous_and_next_link($router)
    {
        // links
        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'optionD', 'search_before' => null, 'limit'=> 2],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=optionD');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit'=> 2, 'search_after' => null, 'search_before' => null],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => null, 'search_before' => 'optionE', 'limit'=> 2],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_before=optionE');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'optionF', 'search_before' => null, 'limit'=> 2],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=optionF');

        // embedded
        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionE', 'search_after' => null, 'search_before' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionE');

        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionF', 'search_after' => null, 'search_before' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionF');

        $standardItems = [
            ['code'   => 'optionE'],
            ['code'   => 'optionF'],
        ];

        $expectedItems = [
            '_links'       => [
                'self'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=optionD',
                ],
                'first'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2',
                ],
                'previous'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_before=optionE',
                ],
                'next'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=optionF',
                ],
            ],
            'current_page' => null,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionE',
                            ],
                        ],
                        'code'   => 'optionE',
                    ],
                    [
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionF',
                            ],
                        ],
                        'code'   => 'optionF',
                    ],
                ],
            ],
        ];

        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after', 'search_after' => 'optionD'],
            'search_after'        => ['self' => 'optionD', 'next' => 'optionF', 'previous' => 'optionE'],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];

        $this->paginate($standardItems, $parameters, null)->shouldReturn($expectedItems);
    }

    function it_paginates_without_next_link_when_last_page($router)
    {
        // links
        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'a_text', 'search_before' => null, 'limit'=> 2],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit'=> 2, 'search_after' => null, 'search_before' => 'optionA'],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_before=optionA');

        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit'=> 2, 'search_after' => null, 'search_before' => null],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2');

        // embedded
        $router
            ->generate(
                'attribute_option_item_route',
                ['attributeCode' => 'a_multi_select', 'code' => 'optionA', 'search_after' => null, 'search_before' => null],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
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
                'previous'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_before=optionA',
                ]
            ],
            'current_page' => null,
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
            'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after', 'search_after' => 'a_text'],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
            'search_after'        => ['self' => 'a_text', 'next' => '', 'previous' => 'optionA'],
        ];

        $this->paginate($standardItems, $parameters, null)->shouldReturn($expectedItems);
    }

    function it_paginates_with_one_page_when_total_items_equals_zero($router)
    {
        $router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit'=> 2, 'search_after' => null, 'search_before' => null],
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
            'current_page' => null,
            '_embedded'    => [
                'items' => [],
            ],
        ];

        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after'],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
            'search_after'        => ['self' => '', 'next' => '', 'previous' => ''],
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
}
