<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EmptyValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EmptyValueCommandFactory;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class EmptyValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EmptyValueCommandFactory::class);
    }

    function it_only_supports_create_empty_value_of_any_attributes(ImageAttribute $image, TextAttribute $text)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'fr_FR',
            'data'    => null
        ];

        $this->supports($image, $normalizedValue)->shouldReturn(true);
        $this->supports($text, $normalizedValue)->shouldReturn(true);
    }

    function it_creates_file_value(ImageAttribute $imageAttribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'fr_FR',
            'data' => null
        ];
        $command = $this->create($imageAttribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EmptyValueCommand::class);
        $command->attribute->shouldBeEqualTo($imageAttribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('fr_FR');
    }
}
