<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditNumberValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditNumberValueCommandFactory;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use PhpSpec\ObjectBehavior;

class EditNumberValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditNumberValueCommandFactory::class);
    }

    function it_only_supports_create_value_of_number_attribute(
        ImageAttribute $image,
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
