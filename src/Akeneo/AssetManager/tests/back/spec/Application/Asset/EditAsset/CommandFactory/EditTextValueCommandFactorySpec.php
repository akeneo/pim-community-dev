<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommandFactory;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
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
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => 'A description'
        ];

        $this->supports($image, $normalizedValue)->shouldReturn(false);
        $this->supports($text, $normalizedValue)->shouldReturn(true);
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
