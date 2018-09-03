<?php

namespace spec\Akeneo\Tool\Component\Api\Normalizer;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Api\Normalizer\FileNormalizer;
use Prophecy\Argument;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FileNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer, RouterInterface $router)
    {
        $this->beConstructedWith($stdNormalizer, $router);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FileNormalizer::class);
    }

    function it_supports_a_file(FileInfoInterface $fileInfo)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($fileInfo, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($fileInfo, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_file($stdNormalizer, $router, FileInfoInterface $fileInfo)
    {
        $data = [
            'code'              => 'f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt',
            'original_filename' => 'file a',
            'mime_type'         => 'plain/text',
            'size'              => 2355,
            'extension'         => 'txt',
            '_links'            => [
                'download' => [
                    'href' => 'http://localhost/api/rest/v1/media_files/f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt/download'
                ]
            ]
        ];

        $router->generate(
            'pim_api_media_file_download',
            ['code' => 'f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt'],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://localhost/api/rest/v1/media_files/f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt/download');

        $stdNormalizer->normalize($fileInfo, 'standard', [])->willReturn($data);

        $this->normalize($fileInfo, 'external_api', [])->shouldReturn($data);
    }
}
