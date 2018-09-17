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


    function it_supports_text_attributes(TextAttribute $text, ImageAttribute $image)
    {
        $this->supports($image)->shouldReturn(true);
        $this->supports($text)->shouldReturn(false);
    }

    function it_hydrates_text_data(ImageAttribute $image)
    {
        $imageData = $this->hydrate([
            'original_filename' => 'my_image.png',
            'file_key'          => '/a/file/key',
        ], $image);
        $imageData->shouldBeAnInstanceOf(FileData::class);
        $imageData->normalize()->shouldReturn([
            'file_key'          => '/a/file/key',
            'original_filename' => 'my_image.png',
        ]);
    }
}
