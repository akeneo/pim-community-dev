<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AppendOptionCollectionValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AppendOptionCollectionValueCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class AppendOptionCollectionValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_a_value_command_factory()
    {
        $this->shouldBeAnInstanceOf(EditValueCommandFactoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(AppendOptionCollectionValueCommandFactory::class);
    }

    function it_supports_the_option_collection_attribute_with_action_replace(
        OptionCollectionAttribute $optionCollectionAttribute,
        TextAttribute $textAttribute
    ) {
        $normalizedValue = ['data' => ['blue', 'red'], 'action' => 'append'];
        $this->supports($optionCollectionAttribute, $normalizedValue)->shouldReturn(true);
        $this->supports($textAttribute, $normalizedValue)->shouldReturn(false);

        $normalizedValue = ['data' => ['blue', 'red']];
        $this->supports($optionCollectionAttribute, $normalizedValue)->shouldReturn(false);
        $normalizedValue = ['data' => ['blue', 'red', 'action' => 'replace']];
        $this->supports($optionCollectionAttribute, $normalizedValue)->shouldReturn(false);

        $normalizedValue = ['data' => [], 'action' => 'append'];
        $this->supports($optionCollectionAttribute, $normalizedValue)->shouldReturn(true);
        $normalizedValue = ['data' => 'blue', 'action' => 'append'];
        $this->supports($optionCollectionAttribute, $normalizedValue)->shouldReturn(false);
    }

    function it_creates_an_append_option_collection_value_command(
        OptionCollectionAttribute $optionCollectionAttribute
    ) {
        $normalizedValue = [
            'data' => ['blue', 'red'],
            'channel' => 'mobile',
            'locale' => 'fr_FR',
            'action' => 'append',
        ];

        $command = $this->create($optionCollectionAttribute, $normalizedValue);
        $command->shouldBeAnInstanceOf(AppendOptionCollectionValueCommand::class);
        $command->attribute->shouldBeAnInstanceOf(OptionCollectionAttribute::class);
        $command->channel->shouldBe('mobile');
        $command->locale->shouldBe('fr_FR');
    }

    function it_creates_an_empty_append_option_collection_value_command(
        OptionCollectionAttribute $optionCollectionAttribute
    ) {
        $normalizedValue = [
            'data' => null,
            'channel' => 'mobile',
            'locale' => 'fr_FR',
            'action' => 'append',
        ];

        $command = $this->create($optionCollectionAttribute, $normalizedValue);
        $command->shouldBeAnInstanceOf(AppendOptionCollectionValueCommand::class);
        $command->attribute->shouldBeAnInstanceOf(OptionCollectionAttribute::class);
        $command->channel->shouldBe('mobile');
        $command->locale->shouldBe('fr_FR');
        $command->optionCodes->shouldBe([]);
    }
}
