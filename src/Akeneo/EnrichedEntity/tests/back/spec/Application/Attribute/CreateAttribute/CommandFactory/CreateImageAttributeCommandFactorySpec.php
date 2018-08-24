<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateImageAttributeCommandFactory;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use PhpSpec\ObjectBehavior;

class CreateImageAttributeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateImageAttributeCommandFactory::class);
    }

    function it_only_supports_attribute_type_image()
    {
        $this->supports(['type' => 'image'])->shouldReturn(true);
        $this->supports(['type' => 'text'])->shouldReturn(false);
    }

    function it_creates_a_command_to_create_an_image_attribute()
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
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'max_file_size' => '1512.12',
            'allowed_extensions' => ['pdf', 'png'],
        ]);
        $command->shouldBeAnInstanceOf(CreateImageAttributeCommand::class);
        $command->identifier->shouldBeEqualTo([
            'identifier'                 => 'name',
            'enriched_entity_identifier' => 'designer',
        ]);
        $command->enrichedEntityIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('name');
        $command->labels->shouldBeEqualTo(['fr_FR' => 'Nom']);
        $command->order->shouldBeEqualTo(1);
        $command->isRequired->shouldBeEqualTo(false);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
        $command->maxFileSize->shouldBeEqualTo('1512.12');
        $command->allowedExtensions->shouldBeEqualTo(['pdf', 'png']);
    }

    function it_creates_a_command_with_a_null_property_if_the_value_is_missing()
    {
        $command = $this->create([]);

        $command->shouldBeAnInstanceOf(CreateImageAttributeCommand::class);
        $command->identifier->shouldBeEqualTo([
            'identifier'                 => null,
            'enriched_entity_identifier' => null,
        ]);
        $command->enrichedEntityIdentifier->shouldBeEqualTo(null);
        $command->code->shouldBeEqualTo(null);
        $command->labels->shouldBeEqualTo(null);
        $command->order->shouldBeEqualTo(null);
        $command->isRequired->shouldBeEqualTo(false);
        $command->valuePerChannel->shouldBeEqualTo(null);
        $command->valuePerLocale->shouldBeEqualTo(null);
        $command->maxFileSize->shouldBeEqualTo(null);
        $command->allowedExtensions->shouldBeEqualTo([]);
    }
}
