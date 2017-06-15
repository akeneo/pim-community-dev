<?php

namespace spec\Pim\Component\Enrich\Converter\StandardToEnrich;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

class ValueConverterSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository, FileInfoRepositoryInterface $fileInfoRepository)
    {
        $this->beConstructedWith($attributeRepository, $fileInfoRepository);
    }

    function it_converts_media($attributeRepository, $fileInfoRepository, FileInfoInterface $fileInfo)
    {
        $attributeRepository->findMediaAttributeCodes()->willReturn(['picture']);
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
