<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\MediaFileDataHydrator;
use PhpSpec\ObjectBehavior;

class MediaFileDataHydratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MediaFileDataHydrator::class);
    }


    function it_only_supports_hydrate_data_of_media_file_attribute(
        TextAttribute $textAttribute,
        MediaFileAttribute $mediaFileAttribute
    ) {
        $this->supports($mediaFileAttribute)->shouldReturn(true);
        $this->supports($textAttribute)->shouldReturn(false);
    }

    function it_hydrates_media_file_data(MediaFileAttribute $mediaFileAttribute)
    {
        $mediaFileData = $this->hydrate([
            'filePath' => '/a/file/key',
            'originalFilename' => 'my_image.png',
            'size' => 1024,
            'mimeType' => 'image/png',
            'extension' => 'png',
            'updatedAt' => '2019-11-22T15:01:22+0000'
        ], $mediaFileAttribute);
        $mediaFileData->shouldBeAnInstanceOf(FileData::class);
        $mediaFileData->normalize()->shouldReturn([
            'filePath'          => '/a/file/key',
            'originalFilename' => 'my_image.png',
            'size' => 1024,
            'mimeType' => 'image/png',
            'extension' => 'png',
            'updatedAt' => '2019-11-22T15:01:22+0000'
        ]);
    }
}
