<?php

namespace spec\Akeneo\Tool\Component\Api\Hal;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Api\Hal\HalResource;
use Akeneo\Tool\Component\Api\Hal\Link;

class HalResourceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([], [], []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(HalResource::class);
    }

    function it_generates_an_hal_array_with_links_and_data_and_embedded_resources(
        HalResource $resource,
        Link $self,
        Link $href
    ) {
        $self->toArray()->willReturn(
            [
                'self' => [
                    'href' => 'http://akeneo.com/self'
                ]
            ]
        );

        $href->toArray()->willReturn(
            [
                'next' => [
                    'href' => 'http://akeneo.com/next',
                ]
            ]
        );

        $resource->toArray()->willReturn(
            [
                '_links' => [
                    'self' => [
                        'href' => 'http://akeneo.com/api/resource/id',
                    ],
                ],
                'data'   => 'item_data',
            ]
        );

        $this->beConstructedWith([$self, $href], ['items' => [$resource]], ['total_items' => 1]);

        $this->toArray()->shouldReturn(
            [
                '_links'      => [
                    'self' => [
                        'href' => 'http://akeneo.com/self',
                    ],
                    'next' => [
                        'href' => 'http://akeneo.com/next',
                    ],
                ],
                'total_items' => 1,
                '_embedded'   => [
                    'items' => [
                        [
                            '_links' => [
                                'self' => [
                                    'href' => 'http://akeneo.com/api/resource/id',
                                ],
                            ],
                            'data'   => 'item_data',
                        ],
                    ],
                ],
            ]
        );
    }

    function it_generates_an_hal_array_without_any_embedded_resources(Link $link)
    {
        $link->toArray()->willReturn(
            [
                'next' => [
                    'href' => 'http://akeneo.com/next',
                ],
            ]
        );

        $this->beConstructedWith([$link], [], ['total_items' => 1]);

        $this->toArray()->shouldReturn(
            [
                '_links'      => [
                    'next' => [
                        'href' => 'http://akeneo.com/next',
                    ],
                ],
                'total_items' => 1,
            ]
        );
    }

    function it_generates_an_array_without_link_or_embedded_resources()
    {
        $this->beConstructedWith([], [], []);

        $this->toArray()->shouldReturn(
            [
            ]
        );
    }

    function it_generates_an_hal_array_with_an_empty_list_of_embedded_resources()
    {
        $this->beConstructedWith([], ['items' => []], []);

        $this->toArray()->shouldReturn(
            [
                '_embedded'   => [
                    'items' => [],
                ],
            ]
        );
    }

    function it_generates_an_hal_array_with_links_in_embedded_resources(HalResource $resource, Link $self, Link $href)
    {
        $self->toArray()->willReturn(
            [
                'self' => [
                    'href' => 'http://akeneo.com/self'
                ]
            ]
        );

        $href->toArray()->willReturn(
            [
                'next' => [
                    'href' => 'http://akeneo.com/next',
                ],
            ]
        );

        $resource->toArray()->willReturn(
            [
                '_links' => [
                    'self' => [
                        'href' => 'http://akeneo.com/api/resource/id',
                    ],
                    'download' => [
                        'href' => 'http://akeneo.com/api/resource/download',
                    ]
                ],
                'data'   => 'item_data',
            ]
        );

        $this->beConstructedWith([$self, $href], ['items' => [$resource]], ['total_items' => 1]);

        $this->toArray()->shouldReturn(
            [
                '_links'      => [
                    'self' => [
                        'href' => 'http://akeneo.com/self',
                    ],
                    'next' => [
                        'href' => 'http://akeneo.com/next',
                    ],
                ],
                'total_items' => 1,
                '_embedded'   => [
                    'items' => [
                        [
                            '_links' => [
                                'self' => [
                                    'href' => 'http://akeneo.com/api/resource/id',
                                ],
                                'download' => [
                                    'href' => 'http://akeneo.com/api/resource/download',
                                ]
                            ],
                            'data'   => 'item_data',
                        ],
                    ],
                ],
            ]
        );
    }
}
