<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory\TextAttributeFactory;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use PhpSpec\ObjectBehavior;

class TextAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TextAttributeFactory::class);
    }

    function it_only_supports_create_text_commands()
    {
        $this->supports(new CreateTextAttributeCommand())->shouldReturn(true);
        $this->supports(new CreateImageAttributeCommand())->shouldReturn(false);
    }

    function it_creates_text_attribute_with_command()
    {
        $command = new CreateTextAttributeCommand();
        $command->identifier = [
            'identifier' => 'name',
            'enriched_entity_identifier' => 'designer'
        ];
        $command->enrichedEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = [
            'fr_FR' => 'Nom'
        ];
        $command->order = 0;
        $command->required = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = 155;

       $this->create($command)->normalize()->shouldReturn([
           'identifier' => [
               'enriched_entity_identifier' => 'designer',
               'identifier' => 'name'
           ],
           'enriched_entity_identifier' => 'designer',
           'code' => 'name',
           'labels' => ['fr_FR' => 'Nom'],
           'order' => 0,
           'required' => true,
           'value_per_channel' => false,
           'value_per_locale' => false,
           'type' => 'text',
           'max_length' => 155,
       ]);
    }

    function it_creates_a_text_attribute_with_infinite_max_length()
    {
        $command = new CreateTextAttributeCommand();
        $command->identifier = [
            'identifier' => 'name',
            'enriched_entity_identifier' => 'designer'
        ];
        $command->enrichedEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = [
            'fr_FR' => 'Nom'
        ];
        $command->order = 0;
        $command->required = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = AttributeMaxLength::NO_LIMIT;

        $this->create($command)->normalize()->shouldReturn([
            'identifier' => [
                'enriched_entity_identifier' => 'designer',
                'identifier' => 'name'
            ],
            'enriched_entity_identifier' => 'designer',
            'code' => 'name',
            'labels' => ['fr_FR' => 'Nom'],
            'order' => 0,
            'required' => true,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'type' => 'text',
            'max_length' => null,
        ]);
    }
}
