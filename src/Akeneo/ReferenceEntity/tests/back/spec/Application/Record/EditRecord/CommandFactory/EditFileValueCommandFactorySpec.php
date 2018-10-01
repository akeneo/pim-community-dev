<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditFileValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditFileValueCommandFactory;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class EditFileValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditFileValueCommandFactory::class);
    }

    function it_only_supports_create_value_of_image_attribute(ImageAttribute $image, TextAttribute $text)
    {
        $this->supports($image)->shouldReturn(true);
        $this->supports($text)->shouldReturn(false);
    }

    function it_creates_file_value(ImageAttribute $imageAttribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'fr_FR',
            'data'    => [
                'filePath'         => '/a/file/path/my_image.png',
                'originalFilename' => 'my_image.png',
            ],
        ];
        $command = $this->create($imageAttribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EditFileValueCommand::class);
        $command->attribute->shouldBeEqualTo($imageAttribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('fr_FR');
        $command->filePath->shouldBeEqualTo('/a/file/path/my_image.png');
        $command->originalFilename->shouldBeEqualTo('my_image.png');
    }
}
