<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditOptionValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditOptionValueCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
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
