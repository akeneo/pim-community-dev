<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory\RecordAttributeFactory;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateRecordAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use PhpSpec\ObjectBehavior;

class RecordAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RecordAttributeFactory::class);
    }

    function it_only_supports_create_text_commands()
    {
        $this->supports(new CreateRecordAttributeCommand())->shouldReturn(true);
        $this->supports(new CreateImageAttributeCommand())->shouldReturn(false);
    }

    function it_creates_a_record_attribute_with_a_command(AttributeIdentifier $identifier)
    {
        $command = new CreateRecordAttributeCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->code = 'mentor';
        $command->labels = ['fr_FR' => 'Mentor'];
        $command->order = 0;
        $command->isRequired = false;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->recordType = 'designer';

        $identifier->__toString()->willReturn('mentor_designer_fingerprint');

        $this->create($command, $identifier)->normalize()->shouldReturn([
            'identifier' => 'mentor_designer_fingerprint',
            'reference_entity_identifier' => 'designer',
            'code' => 'mentor',
            'labels' => ['fr_FR' => 'Mentor'],
            'order' => 0,
            'is_required' => false,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'type' => 'record',
            'record_type' => 'designer',
        ]);
    }
}
