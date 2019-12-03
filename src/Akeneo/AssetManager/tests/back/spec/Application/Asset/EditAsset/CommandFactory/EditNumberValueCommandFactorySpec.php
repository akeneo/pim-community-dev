<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditNumberValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditNumberValueCommandFactory;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use PhpSpec\ObjectBehavior;

class EditNumberValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditNumberValueCommandFactory::class);
    }

    function it_only_supports_create_value_of_number_attribute(
        MediaFileAttribute $image,
        NumberAttribute $number
    ) {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => '250'
        ];

        $this->supports($image, $normalizedValue)->shouldReturn(false);
        $this->supports($number, $normalizedValue)->shouldReturn(true);
    }

    function it_creates_number_value(NumberAttribute $numberAttribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => '250'
        ];
        $command = $this->create($numberAttribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EditNumberValueCommand::class);
        $command->attribute->shouldBeEqualTo($numberAttribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('en_US');
        $command->number->shouldBeEqualTo('250');
    }
}
