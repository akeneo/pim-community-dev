<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Converter\InternalApiToStandard;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ValueConverterSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_converts_media($attributeRepository)
    {
        $attributeRepository->findMediaAttributeCodes()->willReturn(['picture']);

        $enrichFormat = [
            'picture' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        'filePath'         => '/tmp/my_picture.jpg',
                        'originalFilename' => 'my_picture.jpg'
                    ]
                ]
            ],
            'text' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'a simple text'
                ]
            ]
        ];

        $standardFormat = [
            'picture' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => '/tmp/my_picture.jpg',
                ]
            ],
            'text' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'a simple text'
                ]
            ]
        ];

        $this->convert($enrichFormat)->shouldReturn($standardFormat);
    }
}
