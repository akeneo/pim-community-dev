<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Converter\StandardToInternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ValueConverterSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository, FileInfoRepositoryInterface $fileInfoRepository)
    {
        $this->beConstructedWith($attributeRepository, $fileInfoRepository);
    }

    function it_converts_media($attributeRepository, $fileInfoRepository, FileInfoInterface $fileInfo)
    {
        $attributeRepository->getAttributeTypeByCodes(['picture', 'text'])->willReturn([
            'picture' => AttributeTypes::IMAGE,
            'text' => AttributeTypes::TEXT
        ]);
        $fileInfoRepository->findOneByIdentifier('/a/b/c/my_picture.jpg')->willReturn($fileInfo);
        $fileInfo->getOriginalFilename()->willReturn('My picture.jpg');

        $standardFormat = [
            'picture' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => '/a/b/c/my_picture.jpg',
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

        $enrichFormat = [
            'picture' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        'filePath'         => '/a/b/c/my_picture.jpg',
                        'originalFilename' => 'My picture.jpg'
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

        $this->convert($standardFormat)->shouldReturn($enrichFormat);
    }
}
