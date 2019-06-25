<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaLinkValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaLinkValueCommandFactory;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use PhpSpec\ObjectBehavior;

class EditMediaLinkValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditMediaLinkValueCommandFactory::class);
    }

    function it_only_supports_create_value_of_mediaLink_attribute(
        ImageAttribute $image,
        MediaLinkAttribute $mediaLink
    ) {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => 'A description'
        ];

        $this->supports($image, $normalizedValue)->shouldReturn(false);
        $this->supports($mediaLink, $normalizedValue)->shouldReturn(true);
    }

    function it_creates_text_value(MediaLinkAttribute $textAttribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => 'house_255311'
        ];
        $command = $this->create($textAttribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EditMediaLinkValueCommand::class);
        $command->attribute->shouldBeEqualTo($textAttribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('en_US');
        $command->mediaLink->shouldBeEqualTo('house_255311');
    }
}
