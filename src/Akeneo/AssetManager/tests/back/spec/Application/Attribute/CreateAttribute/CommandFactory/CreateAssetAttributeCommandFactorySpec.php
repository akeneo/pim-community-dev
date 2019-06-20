<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateRecordAttributeCommandFactory;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateRecordAttributeCommand;
use PhpSpec\ObjectBehavior;

class CreateRecordAttributeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateRecordAttributeCommandFactory::class);
    }

    function it_only_supports_attribute_type_record()
    {
        $this->supports(['type' => 'record'])->shouldReturn(true);
        $this->supports(['type' => 'image'])->shouldReturn(false);
    }

    function it_creates_a_command_to_create_a_record_attribute()
    {
        $command = $this->create([
            'reference_entity_identifier' => 'designer',
            'code' => 'mentor',
            'labels' => ['fr_FR' => 'Mentor'],
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'record_type' => 'designer',
        ]);

        $command->shouldBeAnInstanceOf(CreateRecordAttributeCommand::class);
        $command->referenceEntityIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('mentor');
        $command->labels->shouldBeEqualTo(['fr_FR' => 'Mentor']);
        $command->isRequired->shouldBeEqualTo(false);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
        $command->recordType->shouldBeEqualTo('designer');
    }

    function it_throws_an_exception_if_there_is_one_missing_common_property()
    {
        $command = [
            'reference_entity_identifier' => 'designer',
            'code' => 'mentor',
            'is_required' => false,
            //'value_per_channel' => false, // For the test purpose, this one is missing
            'value_per_locale' => false,
            'record_type' => 'designer',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }

    function it_throws_an_exception_if_there_is_one_missing_additional_property()
    {
        $command = [
            'reference_entity_identifier' => 'designer',
            'code' => 'mentor',
            'labels' => ['fr_FR' => 'Mentor'],
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            // 'record_type' => 'designer', // For the test purpose, this one is missing
        ];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }
}
