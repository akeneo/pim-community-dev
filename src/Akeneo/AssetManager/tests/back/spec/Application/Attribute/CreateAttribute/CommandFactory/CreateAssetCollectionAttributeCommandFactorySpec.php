<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateRecordCollectionAttributeCommandFactory;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateRecordCollectionAttributeCommand;
use PhpSpec\ObjectBehavior;

class CreateRecordCollectionAttributeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateRecordCollectionAttributeCommandFactory::class);
    }

    function it_only_supports_attribute_type_record_collection()
    {
        $this->supports(['type' => 'record_collection'])->shouldReturn(true);
        $this->supports(['type' => 'image'])->shouldReturn(false);
    }

    function it_creates_a_command_to_create_a_record_collection_attribute()
    {
        $command = $this->create([
            'reference_entity_identifier' => 'designer',
            'code' => 'brands',
            'labels' => ['fr_FR' => 'Marques'],
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'record_type' => 'brand',
        ]);

        $command->shouldBeAnInstanceOf(CreateRecordCollectionAttributeCommand::class);
        $command->referenceEntityIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('brands');
        $command->labels->shouldBeEqualTo(['fr_FR' => 'Marques']);
        $command->isRequired->shouldBeEqualTo(true);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
        $command->recordType->shouldBeEqualTo('brand');
    }

    function it_throws_an_exception_if_there_is_one_missing_common_property()
    {
        $command = [
            'reference_entity_identifier' => 'designer',
            // 'code' => 'brands', // For the test purpose, this one is missing
            'labels' => ['fr_FR' => 'Marques'],
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'record_type' => 'brand',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }

    function it_throws_an_exception_if_there_is_one_missing_additional_property()
    {
        $command = [
            'reference_entity_identifier' => 'designer',
            'code' => 'brands',
            'labels' => ['fr_FR' => 'Marques'],
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => false,
            // 'record_type' => 'brand', // For the test purpose, this one is missing
        ];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }
}
