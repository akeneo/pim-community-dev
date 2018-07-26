<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\Factory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\Command\CreateImageAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\Command\CreateTextAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\Factory\TextAttributeFactory;
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
           'max_length' => 155,
       ]);
    }
}
