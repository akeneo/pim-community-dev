<?php

namespace spec\Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory\TextAttributeFactory;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegex;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
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

    function it_creates_a_simple_text_attribute_with_a_command()
    {
        $command = new CreateTextAttributeCommand();
        $command->identifier = ['identifier' => 'name', 'enriched_entity_identifier' => 'designer'];
        $command->enrichedEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = ['fr_FR' => 'Nom'];
        $command->order = 0;
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = 155;
        $command->isTextArea = false;
        $command->validationRule = AttributeValidationRule::NONE;
        $command->regularExpression = AttributeRegex::NONE;

        $this->create($command)->normalize()->shouldReturn([
            'identifier'                 => [
                'enriched_entity_identifier' => 'designer',
                'identifier'                 => 'name',
            ],
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => 155,
            'is_text_area'               => false,
            'is_rich_text_editor'        => false,
            'valdiation_rule'            => null,
            'regular_expression'         => null,
        ]);
    }

    function it_creates_a_simple_text_attribute_having_a_validation_with_a_command()
    {
        $command = new CreateTextAttributeCommand();
        $command->identifier = ['identifier' => 'name', 'enriched_entity_identifier' => 'designer'];
        $command->enrichedEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = ['fr_FR' => 'Nom'];
        $command->order = 0;
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = 155;
        $command->isTextArea = false;
        $command->validationRule = AttributeValidationRule::NONE;
        $command->regularExpression = AttributeRegex::NONE;

        $this->create($command)->normalize()->shouldReturn([
            'identifier'                 => ['enriched_entity_identifier' => 'designer', 'identifier' => 'name'],
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => 155,
            'is_text_area'               => false,
            'is_rich_text_editor'        => false,
            'valdiation_rule'            => null,
            'regular_expression'         => null,
        ]);
    }

    function it_creates_a_simple_text_attribute_with_infinite_max_length()
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
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = AttributeMaxLength::NO_LIMIT;

        $this->create($command)->normalize()->shouldReturn([
            'identifier'                 => [
                'enriched_entity_identifier' => 'designer',
                'identifier'                 => 'name',
            ],
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => null,
            'is_text_area'               => false,
            'is_rich_text_editor'        => false,
            'valdiation_rule'            => null,
            'regular_expression'         => null,
        ]);
    }

    function it_creates_a_simple_text_attribute_with_a_regular_expression_validation_()
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
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = AttributeMaxLength::NO_LIMIT;
        $command->isTextArea = false;
        $command->validationRule = AttributeValidationRule::REGULAR_EXPRESSION;
        $command->regularExpression = '/\w+/';

        $this->create($command)->normalize()->shouldReturn([
            'identifier'                 => [
                'enriched_entity_identifier' => 'designer',
                'identifier'                 => 'name',
            ],
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => null,
            'is_text_area'               => false,
            'is_rich_text_editor'        => false,
            'valdiation_rule'            => 'regular_expression',
            'regular_expression'         => '/\w+/',
        ]);
    }

    function it_creates_a_text_area_with_rich_editor()
    {
        $command = new CreateTextAttributeCommand();
        $command->identifier = ['identifier' => 'name', 'enriched_entity_identifier' => 'designer'];
        $command->enrichedEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = ['fr_FR' => 'Nom'];
        $command->order = 0;
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxLength = 155;
        $command->isTextArea = true;
        $command->isRichTextEditor = true;

        $this->create($command)->normalize()->shouldReturn([
            'identifier'                 => ['enriched_entity_identifier' => 'designer', 'identifier' => 'name'],
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => ['fr_FR' => 'Nom'],
            'order'                      => 0,
            'is_required'                => true,
            'value_per_channel'          => false,
            'value_per_locale'           => false,
            'type'                       => 'text',
            'max_length'                 => 155,
            'is_text_area'               => true,
            'is_rich_text_editor'        => true,
            'valdiation_rule'            => null,
            'regular_expression'         => null,
        ]);
    }
}
