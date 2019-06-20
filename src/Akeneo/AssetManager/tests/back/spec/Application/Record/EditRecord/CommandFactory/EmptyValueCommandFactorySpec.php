<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EmptyValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EmptyValueCommandFactory;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use PhpSpec\ObjectBehavior;

class EmptyValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EmptyValueCommandFactory::class);
    }

    function it_only_supports_create_empty_value_of_any_attributes(AbstractAttribute $attribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'fr_FR',
            'data'    => null
        ];

        $this->supports($attribute, $normalizedValue)->shouldReturn(true);

        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'fr_FR',
            'data'    => ''
        ];

        $this->supports($attribute, $normalizedValue)->shouldReturn(true);

        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'fr_FR',
            'data'    => []
        ];

        $this->supports($attribute, $normalizedValue)->shouldReturn(true);
    }

    function it_creates_empty_value(AbstractAttribute $attribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'fr_FR',
            'data' => null
        ];
        $command = $this->create($attribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EmptyValueCommand::class);
        $command->attribute->shouldBeEqualTo($attribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('fr_FR');
    }
}
