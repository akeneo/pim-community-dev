<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommandFactory;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class EditTextValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditTextValueCommandFactory::class);
    }

    function it_only_supports_create_value_of_text_attribute(
        ImageAttribute $image,
        TextAttribute $text
    ) {
        $this->supports($image, [])->shouldReturn(false);
        $this->supports($text, [])->shouldReturn(true);
    }

    function it_creates_text_value(TextAttribute $textAttribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => 'A description'
        ];
        $command = $this->create($textAttribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EditTextValueCommand::class);
        $command->attribute->shouldBeEqualTo($textAttribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('en_US');
        $command->text->shouldBeEqualTo('A description');
    }
}
