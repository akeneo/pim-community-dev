<?php

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory\ImageAttributeFactory;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
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

    function it_creates_an_image_attribute_with_command()
    {
        $command = new CreateImageAttributeCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = [
            'fr_FR' => 'Nom'
        ];
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxFileSize = '30.0';
        $command->allowedExtensions = ['pdf', 'png'];

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier' => 'name_designer_test',
            'reference_entity_identifier' => 'designer',
            'code' => 'name',
            'labels' => ['fr_FR' => 'Nom'],
            'order' => 0,
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'type' => 'image',
            'max_file_size' => '30.0',
            'allowed_extensions' => ['pdf', 'png'],
        ]);
    }

    function it_creates_an_image_attribute_with_no_max_file_size_limit()
    {
        $command = new CreateImageAttributeCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = [
            'fr_FR' => 'Nom'
        ];
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxFileSize = null;
        $command->allowedExtensions = ['pdf', 'png'];

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier' => 'name_designer_test',
            'reference_entity_identifier' => 'designer',
            'code' => 'name',
            'labels' => ['fr_FR' => 'Nom'],
            'order' => 0,
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'type' => 'image',
            'max_file_size' => null,
            'allowed_extensions' => ['pdf', 'png'],
        ]);
    }

    function it_creates_an_image_attribute_with_extensions_all_allowed()
    {
        $command = new CreateImageAttributeCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->code = 'name';
        $command->labels = [
            'fr_FR' => 'Nom'
        ];
        $command->isRequired = true;
        $command->valuePerChannel = false;
        $command->valuePerLocale = false;
        $command->maxFileSize = null;
        $command->allowedExtensions = AttributeAllowedExtensions::ALL_ALLOWED;

        $this->create(
            $command,
            AttributeIdentifier::fromString('name_designer_test'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier' => 'name_designer_test',
            'reference_entity_identifier' => 'designer',
            'code' => 'name',
            'labels' => ['fr_FR' => 'Nom'],
            'order' => 0,
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'type' => 'image',
            'max_file_size' => null,
            'allowed_extensions' => [],
        ]);
    }
}
