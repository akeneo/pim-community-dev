<?php

namespace spec\Pim\Bundle\EnrichBundle\File;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\File\FileTypes;
use Prophecy\Argument;

class DefaultImageProviderSpec extends ObjectBehavior
{
    function let(
        FilterManager $filterManager,
        CacheManager $cacheManager
    ) {
        $defaultImages = [];
        foreach ([FileTypes::DOCUMENT, FileTypes::IMAGE, FileTypes::VIDEO, FileTypes::MISC] as $fileType) {
            $imagePath = $this->getImagePath($fileType);
            touch($imagePath);

            $defaultImages[$fileType] = [
                'path'      => $imagePath,
                'mime_type' => 'image/png',
                'extension' => 'png'
            ];
        }

        $this->beConstructedWith($filterManager, $cacheManager, $defaultImages);
    }

    function it_provides_default_images_for_file_types(
        $filterManager,
        $cacheManager,
        BinaryInterface $binary
    ) {
        $filterManager->applyFilter(Argument::cetera())->willReturn($binary);
        $cacheManager->isStored(Argument::cetera())->willReturn(false);

        $fileKey = $this->getFileKey(FileTypes::IMAGE);
        $cacheManager->store($binary, $fileKey, 'some_filter')->shouldBeCalled();
        $cacheManager->resolve($fileKey, 'some_filter')->shouldBeCalled();
        $this->getImageUrl(FileTypes::IMAGE, 'some_filter');

        $fileKey = $this->getFileKey(FileTypes::DOCUMENT);
        $cacheManager->store($binary, $fileKey, 'some_filter')->shouldBeCalled();
        $cacheManager->resolve($fileKey, 'some_filter')->shouldBeCalled();
        $this->getImageUrl(FileTypes::DOCUMENT, 'some_filter');
    }

    function it_throws_exception_when_providing_an_image_for_undefined_file_type(
        $cacheManager
    ) {
        $cacheManager->isStored(Argument::cetera())->willReturn(false);
        $cacheManager->store(Argument::cetera())->shouldNotBeCalled();
        $cacheManager->resolve(Argument::cetera())->shouldNotBeCalled();
        $this->shouldThrow('\InvalidArgumentException')->during('getImageUrl', ['undefined_type_key', 'some_filter']);
    }

    function letGo()
    {
        foreach ([FileTypes::IMAGE, FileTypes::DOCUMENT, FileTypes::VIDEO, FileTypes::MISC] as $fileType) {
            $imagePath = $this->getImagePath($fileType);
            if (\file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }

    private function getImagePath($fileType)
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileType . '.png';
    }

    private function getFileKey($fileType)
    {
        return sprintf('%s_default_image', $fileType);
    }
}
