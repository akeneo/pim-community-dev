<?php

namespace spec\Pim\Component\Api\Pagination;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Prophecy\Argument;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HalPaginatorSpec extends ObjectBehavior
{
    function let(
        RouterInterface $router
    ) {
        $this->beConstructedWith($router);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Api\Pagination\HalPaginator');
    }

    function it_is_a_paginator()
    {
        $this->shouldImplement('Pim\Component\Api\Pagination\PaginatorInterface');
    }

    function it_paginates_in_hal_format($normalizer, $router)
    {
        // links
        $router
            ->generate('category_list_route', ['page' => 3, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=3');

        $router
            ->generate('category_list_route', ['page' => 1, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=1');

        $router
            ->generate('category_list_route', ['page' => 10, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=10');

        $router
            ->generate('category_list_route', ['page' => 2, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=2');

        $router
            ->generate('category_list_route', ['page' => 4, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=4');

        // embedded
        $router
            ->generate('category_item_route', ['code' => 'master'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/master');

        $router
            ->generate('category_item_route', ['code' => 'sales'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/sales');

        $options = [
            'page'  => 3,
            'limit' => 100,
        ];

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
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=3',
                ],
                'first'    => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                ],
                'last'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=10',
                ],
                'previous' => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=2',
                ],
                'next'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=4',
                ],
            ],
            'current_page' => 3,
            'pages_count'  => 10,
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

        $this
            ->paginate($standardItems, $options, 990, 'category_list_route', 'category_item_route', 'code', [])
            ->shouldReturn($expectedItems);
    }


    function it_paginates_without_previous_link_when_first_page($normalizer, $router)
    {
        // links
        $router
            ->generate('category_list_route', ['page' => 2, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=2');

        $router
            ->generate('category_list_route', ['page' => 1, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=1');

        $router
            ->generate('category_list_route', ['page' => 10, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=10');

        // embedded
        $router
            ->generate('category_item_route', ['code' => 'master'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/master');

        $router
            ->generate('category_item_route', ['code' => 'sales'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/sales');

        $options = [
            'page'  => 1,
            'limit' => 100,
        ];

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
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                ],
                'first' => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                ],
                'last'  => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=10',
                ],
                'next'  => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=2',
                ],
            ],
            'current_page' => 1,
            'pages_count'  => 10,
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

        $this
            ->paginate($standardItems, $options, 990, 'category_list_route', 'category_item_route', 'code', [])
            ->shouldReturn($expectedItems);
    }

    function it_paginates_without_next_link_when_last_page($normalizer, $router)
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

        $options = [
            'page'  => 10,
            'limit' => 100,
        ];

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
                'last'     => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=10',
                ],
                'previous' => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=9',
                ],
            ],
            'current_page' => 10,
            'pages_count'  => 10,
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

        $this
            ->paginate($standardItems, $options, 990, 'category_list_route', 'category_item_route', 'code', [])
            ->shouldReturn($expectedItems);
    }

    function it_paginates_without_previous_and_next_link_when_nonexistent_page($normalizer, $router)
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

        $options = [
            'page'  => 11,
            'limit' => 100,
        ];

        $expectedItems = [
            '_links'       => [
                'self'  => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=11',
                ],
                'first' => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                ],
                'last'  => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=10',
                ],
            ],
            'current_page' => 11,
            'pages_count'  => 10,
            'items_count'  => 990,
            '_embedded'    => [
                'items' => [],
            ],
        ];

        $this
            ->paginate([], $options, 990, 'category_list_route', 'category_item_route', 'code', [])
            ->shouldReturn($expectedItems);
    }

    function it_paginates_with_one_page_when_total_items_equals_zero($normalizer, $router)
    {
        // links
        $router
            ->generate('category_list_route', ['page' => 1, 'limit'=> 100], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/categories/?limit=100&page=1');

        $options = [
            'page'  => 1,
            'limit' => 100,
        ];

        $expectedItems = [
            '_links'       => [
                'self'  => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                ],
                'first' => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                ],
                'last'  => [
                    'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                ],
            ],
            'current_page' => 1,
            'pages_count'  => 1,
            'items_count'  => 0,
            '_embedded'    => [
                'items' => [],
            ],
        ];

        $this
            ->paginate([], $options, 0, 'category_list_route', 'category_item_route', 'code', [])
            ->shouldReturn($expectedItems);
    }
}
