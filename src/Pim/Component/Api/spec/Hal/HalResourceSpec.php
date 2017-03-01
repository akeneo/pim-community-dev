<?php

namespace spec\Pim\Component\Api\Hal;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Exception\ReservedPropertyKeyException;
use Pim\Component\Api\Hal\HalResource;
use Pim\Component\Api\Hal\Link;

class HalResourceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('http://akeneo.com/self', [], [], []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Api\Hal\HalResource');
    }

    function it_generates_an_hal_array_with_links_and_data_and_embedded_resources(HalResource $resource, Link $link)
    {


        $link->toArray()->willReturn(
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
                ],
                'data'   => 'item_data',
            ]
        );

        $this->beConstructedWith('http://akeneo.com/self', [$link], ['items' => [$resource]], ['total_items' => 1]);

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

        $this->beConstructedWith('http://akeneo.com/self', [$link], [], ['total_items' => 1]);

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
            ]
        );
    }

    function it_generates_an_array_with_selflink_by_default()
    {
        $this->beConstructedWith('http://akeneo.com/self', [], [], []);

        $this->toArray()->shouldReturn(
            [
                '_links'      => [
                    'self' => [
                        'href' => 'http://akeneo.com/self',
                    ],
                ],
            ]
        );
    }

    function it_generates_an_hal_array_with_an_empty_list_of_embedded_resources()
    {
        $this->beConstructedWith('http://akeneo.com/self', [], ['items' => []], []);

        $this->toArray()->shouldReturn(
            [
                '_links'      => [
                    'self' => [
                        'href' => 'http://akeneo.com/self',
                    ],
                ],
                '_embedded'   => [
                    'items' => [],
                ],
            ]
        );
    }

    function it_throws_an_exception_when_data_use_a_reserved_hal_property()
    {
        $this
            ->shouldThrow(new ReservedPropertyKeyException('Resource data could not contain a reserved HAL property key.'))
            ->during('__construct', ['http://akeneo.com/self', [], ['items' => []], ['_links' => 'links']]);
    }
}
