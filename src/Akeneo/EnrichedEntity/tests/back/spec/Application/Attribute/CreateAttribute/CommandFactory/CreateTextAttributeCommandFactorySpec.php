<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateTextAttributeCommandFactory;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use PhpSpec\ObjectBehavior;

class CreateTextAttributeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateTextAttributeCommandFactory::class);
    }

    function it_only_supports_attribute_type_text()
    {
        $this->supports(['type' => 'text'])->shouldReturn(true);
        $this->supports(['type' => 'image'])->shouldReturn(false);
    }

    function it_creates_a_command_to_create_a_text_attribute()
    {
        $command = $this->create([
            'identifier'  => [
                'identifier'                 => 'name',
                'enriched_entity_identifier' => 'designer',
            ],
            'enriched_entity_identifier' => 'designer',
            'code' => 'name',
            'labels' => ['fr_FR' => 'Nom'],
            'order' => 1,
            'required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'max_length' => 255,
        ]);
        $command->shouldBeAnInstanceOf(CreateTextAttributeCommand::class);
        $command->identifier->shouldBeEqualTo([
            'identifier'                 => 'name',
            'enriched_entity_identifier' => 'designer',
        ]);
        $command->enrichedEntityIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('name');
        $command->labels->shouldBeEqualTo(['fr_FR' => 'Nom']);
        $command->order->shouldBeEqualTo(1);
        $command->required->shouldBeEqualTo(false);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
        $command->maxLength->shouldBeEqualTo(255);
    }

    function it_creates_a_command_with_a_null_property_if_the_value_is_missing()
    {
        $command = $this->create([]);

        $command->shouldBeAnInstanceOf(CreateTextAttributeCommand::class);
        $command->identifier->shouldBeEqualTo([
            'identifier'                 => null,
            'enriched_entity_identifier' => null,
        ]);
        $command->enrichedEntityIdentifier->shouldBeEqualTo(null);
        $command->code->shouldBeEqualTo(null);
        $command->labels->shouldBeEqualTo(null);
        $command->order->shouldBeEqualTo(null);
        $command->required->shouldBeEqualTo(false);
        $command->valuePerChannel->shouldBeEqualTo(null);
        $command->valuePerLocale->shouldBeEqualTo(null);
        $command->maxLength->shouldBeEqualTo(null);
    }
}
