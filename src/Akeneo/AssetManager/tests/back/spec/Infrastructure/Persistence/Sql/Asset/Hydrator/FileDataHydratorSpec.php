<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\FileDataHydrator;
use PhpSpec\ObjectBehavior;

class FileDataHydratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FileDataHydrator::class);
    }


    function it_only_supports_hydrate_data_of_image_attribute(
        TextAttribute $text,
        ImageAttribute $image
    ) {
        $this->supports($image)->shouldReturn(true);
        $this->supports($text)->shouldReturn(false);
    }

    function it_hydrates_image_data(ImageAttribute $image)
    {
        $imageData = $this->hydrate([
            'filePath'          => '/a/file/key',
            'originalFilename' => 'my_image.png',
            'size' => 1024,
            'mimeType' => 'image/png',
            'extension' => 'png',
        ], $image);
        $imageData->shouldBeAnInstanceOf(FileData::class);
        $imageData->normalize()->shouldReturn([
            'filePath'          => '/a/file/key',
            'originalFilename' => 'my_image.png',
            'size' => 1024,
            'mimeType' => 'image/png',
            'extension' => 'png',
        ]);
    }
}
