<?php

namespace spec\Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\FileData;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator\FileDataHydrator;
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
        ]);
        $imageData->shouldBeAnInstanceOf(FileData::class);
        $imageData->normalize()->shouldReturn([
            'filePath'          => '/a/file/key',
            'originalFilename' => 'my_image.png',
        ]);
    }
}
