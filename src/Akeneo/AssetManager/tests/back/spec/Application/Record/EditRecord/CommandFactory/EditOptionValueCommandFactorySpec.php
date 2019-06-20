<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditOptionValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditOptionValueCommandFactory;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditValueCommandFactoryInterface;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class EditOptionValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_a_value_command_factory()
    {
        $this->shouldBeAnInstanceOf(EditValueCommandFactoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(EditOptionValueCommandFactory::class);
    }

    function it_supports_the_option_attribute(
        OptionAttribute $optionAttribute,
        TextAttribute $textAttribute
    ) {
        $normalizedData = ['data' => 'hello'];
        $this->supports($optionAttribute, $normalizedData)->shouldReturn(true);
        $this->supports($textAttribute, $normalizedData)->shouldReturn(false);

        $normalizedData = ['data' => null];
        $this->supports($optionAttribute, $normalizedData)->shouldReturn(false);
        $normalizedData = ['data' => ''];
        $this->supports($optionAttribute, $normalizedData)->shouldReturn(false);
    }

    function it_creates_an_edit_option_value_command(
        OptionAttribute $optionAttribute
    ) {
        $normalizedValue = [
            'data' => 'coton',
            'channel' => 'mobile',
            'locale' => 'fr_FR',
        ];

        $command = $this->create($optionAttribute, $normalizedValue);
        $command->shouldBeAnInstanceOf(EditOptionValueCommand::class);
        $command->attribute->shouldBeAnInstanceOf(OptionAttribute::class);
        $command->channel->shouldBe('mobile');
        $command->locale->shouldBe('fr_FR');
    }
}
