<?php

namespace spec\Akeneo\Tool\Component\Api\Pagination;

use Akeneo\Tool\Component\Api\Pagination\OffsetHalPaginator;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class OffsetHalPaginatorSpec extends ObjectBehavior
{
    function let(
        RouterInterface $router
    ) {
        $this->beConstructedWith($router);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OffsetHalPaginator::class);
    }

    function it_is_a_paginator()
    {
        $this->shouldImplement(PaginatorInterface::class);
    }

    function it_paginates_in_hal_format_without_count($router)
    {
        // links
        $router
            ->generate('attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 3, 'limit'=> 2], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=3');

        $router
            ->generate('attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 1, 'limit'=> 2], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=1');

        $router
            ->generate('attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 2, 'limit'=> 2], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=2');

        $router
            ->generate('attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 4, 'limit'=> 2], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=4');

        // embedded
        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA');

        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionB'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB');

        $standardItems = [
            ['code'   => 'optionA'],
            ['code'   => 'optionB'],
        ];

        $expectedItems = [
            '_links'       => [
                'self'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=3',
                ],
                'first'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=1',
                ],
                'previous' => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=2',
                ],
                'next'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=4',
                ],
            ],
            'current_page' => 3,
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
            'query_parameters'    => ['page' => 3, 'limit' => 2],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];

        $this->paginate($standardItems, $parameters, null)->shouldReturn($expectedItems);
    }

    function it_paginates_in_hal_format_with_count($router)
    {
        // links
        $router
            ->generate('attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 3, 'limit'=> 2], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=3');

        $router
            ->generate('attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 1, 'limit'=> 2], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=1');

        $router
            ->generate('attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 2, 'limit'=> 2], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=2');

        $router
            ->generate('attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 4, 'limit'=> 2], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=4');

        // embedded
        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA');

        $router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionB'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB');

        $standardItems = [
            ['code'   => 'optionA'],
            ['code'   => 'optionB'],
        ];

        $expectedItems = [
            '_links'       => [
                'self'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=3',
                ],
                'first'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=1',
                ],
                'previous' => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=2',
                ],
                'next'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=4',
                ],
            ],
            'current_page' => 3,
            'items_count'  => 990,
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
            'query_parameters'    => ['page' => 3, 'limit' => 2],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];

        $this->paginate($standardItems, $parameters, 990)->shouldReturn($expectedItems);
    }

    function it_paginates_without_previous_link_when_first_page($router)
    {
        // links
        $router
            ->generate('category_list_route', ['page' => 2, 'limit'=> 2], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=2&page=2');

        $router
            ->generate('category_list_route', ['page' => 1, 'limit'=> 2], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=2&page=1');

        // embedded
        $router
            ->generate('category_item_route', ['code' => 'master'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/master');

        $router
            ->generate('category_item_route', ['code' => 'sales'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/sales');

        $standardItems = [
            [
                'code'   => 'master',
                'parent' => null,
            ],
            [
                'code'   => 'sales',
                'parent' => 'master',
            ],
        ];

        $expectedItems = [
            '_links'       => [
                'self'  => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=2&page=1',
                ],
                'first' => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=2&page=1',
                ],
                'next'  => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=2&page=2',
                ],
            ],
            'current_page' => 1,
            'items_count'  => 990,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/rest/v1/categories/master',
                            ],
                        ],
                        'code'   => 'master',
                        'parent' => null,
                    ],
                    [
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/rest/v1/categories/sales',
                            ],
                        ],
                        'code'   => 'sales',
                        'parent' => 'master',
                    ],
                ],
            ],
        ];

        $parameters = [
            'query_parameters'    => ['page' => 1, 'limit' => 2],
            'list_route_name'     => 'category_list_route',
            'item_route_name'     => 'category_item_route',
        ];

        $this->paginate($standardItems, $parameters, 990)->shouldReturn($expectedItems);
    }

    function it_paginates_without_next_link_when_last_page($router)
    {
        // links
        $router
            ->generate('category_list_route', ['page' => 1, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=1');

        $router
            ->generate('category_list_route', ['page' => 10, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=10');

        $router
            ->generate('category_list_route', ['page' => 9, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=9');

        // embedded
        $router
            ->generate('category_item_route', ['code' => 'master'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/master');

        $router
            ->generate('category_item_route', ['code' => 'sales'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/sales');

        $standardItems = [
            [
                'code'   => 'master',
                'parent' => null,
            ],
            [
                'code'   => 'sales',
                'parent' => 'master',
            ],
        ];

        $expectedItems = [
            '_links'       => [
                'self'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=10',
                ],
                'first'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                ],
                'previous' => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=9',
                ],
            ],
            'current_page' => 10,
            'items_count'  => 990,
            '_embedded'    => [
                'items' => [
                    [
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/rest/v1/categories/master',
                            ],
                        ],
                        'code'   => 'master',
                        'parent' => null,
                    ],
                    [
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/rest/v1/categories/sales',
                            ],
                        ],
                        'code'   => 'sales',
                        'parent' => 'master',
                    ],
                ],
            ],
        ];


        $parameters = [
            'query_parameters'    => ['page' => 10, 'limit' => 100],
            'list_route_name'     => 'category_list_route',
            'item_route_name'     => 'category_item_route',
        ];

        $this->paginate($standardItems, $parameters, 990)->shouldReturn($expectedItems);
    }

    function it_paginates_with_previous_and_without_next_link_when_nonexistent_page($router)
    {
        // links
        $router
            ->generate('category_list_route', ['page' => 11, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=11');

        $router
            ->generate('category_list_route', ['page' => 1, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=1');

        $router
            ->generate('category_list_route', ['page' => 10, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=10');

        $expectedItems = [
            '_links'       => [
                'self'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=11',
                ],
                'first'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                ],
                'previous' => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=10',
                ],
            ],
            'current_page' => 11,
            '_embedded'    => [
                'items' => [],
            ],
        ];

        $parameters = [
            'query_parameters'    => ['page' => 11, 'limit' => 100],
            'list_route_name'     => 'category_list_route',
            'item_route_name'     => 'category_item_route',
        ];

        $this->paginate([], $parameters, null)->shouldReturn($expectedItems);
    }

    function it_paginates_with_one_page_when_total_items_equals_zero($router)
    {
        // links
        $router
            ->generate('category_list_route', ['page' => 1, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=1');

        $expectedItems = [
            '_links'       => [
                'self'  => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                ],
                'first' => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                ]
            ],
            'current_page' => 1,
            'items_count'  => 0,
            '_embedded'    => [
                'items' => [],
            ],
        ];

        $parameters = [
            'query_parameters'    => ['page' => 1, 'limit' => 100],
            'list_route_name'     => 'category_list_route',
            'item_route_name'     => 'category_item_route',
        ];

        $this->paginate([], $parameters, 0)->shouldReturn($expectedItems);
    }

    function it_throws_an_exception_when_unknown_parameter_given()
    {
        $this->shouldThrow(PaginationParametersException::class)->during('paginate', [[], ['foo' => 'bar'], 0]);
    }

    function it_throws_an_exception_when_a_parameter_is_missing()
    {
        $this->shouldThrow(PaginationParametersException::class)->during('paginate', [[], [], 0]);
    }

}
