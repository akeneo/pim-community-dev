<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditEmptyValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditEmptyValueCommandFactory;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class EditEmptyValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditEmptyValueCommandFactory::class);
    }

    function it_only_supports_create_empty_value_of_any_attributes(ImageAttribute $image, TextAttribute $text)
    {
        $this->supports($image)->shouldReturn(true);
        $this->supports($text)->shouldReturn(true);
    }

    function it_creates_file_value(ImageAttribute $imageAttribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'fr_FR'
        ];
        $command = $this->create($imageAttribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EditEmptyValueCommand::class);
        $command->attribute->shouldBeEqualTo($imageAttribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('fr_FR');
    }
}
