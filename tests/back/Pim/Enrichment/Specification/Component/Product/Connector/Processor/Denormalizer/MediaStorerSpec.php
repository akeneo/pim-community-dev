<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Exception\InvalidFile;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MediaStorerSpec extends ObjectBehavior
{
    function let(FileStorerInterface $fileStorer, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($fileStorer, $attributeRepository);
    }

    function it_stores_media_coming_from_raw_values($attributeRepository, $fileStorer)
    {
        $fileInfo = new FileInfo();
        $fileInfo->setKey('file_info_key');

        $attributeRepository->findMediaAttributeCodes()->willReturn(['a_media']);
        $fileStorer->store(Argument::cetera())->willReturn($fileInfo);

        $this->store([
            'a_media' => [
                [
                    'data' => __FILE__
                ],
            ]
        ])->shouldReturn([
            'a_media' => [
                [
                    'data' => 'file_info_key'
                ],
            ]
        ]);
    }

    function it_does_not_alter_non_media_values($attributeRepository)
    {
        $attributeRepository->findMediaAttributeCodes()->willReturn(['a_media']);

        $this->store([
            'color' => [
                [
                    'data' => 'foobar'
                ],
            ]
        ])->shouldReturn([
            'color' => [
                [
                    'data' => 'foobar'
                ],
            ]
        ]);
    }

    function it_throws_an_exception_if_the_path_does_not_exist($fileStorer, $attributeRepository)
    {
        $fileStorer->store(Argument::cetera())->willThrow(InvalidFile::class);
        $attributeRepository->findMediaAttributeCodes()->willReturn(['a_media']);

        $this->shouldThrow(InvalidPropertyException::class)->during('store', [
            [
                'a_media' => [
                    [
                        'data' => '/this/does/not/exist.jpg'
                    ],
                ]
            ]
        ]);
    }
}
