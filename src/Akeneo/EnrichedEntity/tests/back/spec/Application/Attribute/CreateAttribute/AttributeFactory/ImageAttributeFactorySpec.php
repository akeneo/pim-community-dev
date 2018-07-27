<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory\ImageAttributeFactory;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use PhpSpec\ObjectBehavior;

class ImageAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ImageAttributeFactory::class);
    }

    function it_only_supports_create_image_commands()
    {
        $this->supports(new CreateImageAttributeCommand())->shouldReturn(true);
        $this->supports(new CreateTextAttributeCommand())->shouldReturn(false);
    }

    function it_creates_image_attribute_with_command()
    {
        $command = new CreateImageAttributeCommand();
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
        $command->maxFileSize = 30.0;
        $command->allowedExtensions = ['pdf', 'png'];

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
            'type' => 'image',
            'max_file_size' => 30.0,
            'allowed_extensions' => ['pdf', 'png'],
        ]);
    }
}
