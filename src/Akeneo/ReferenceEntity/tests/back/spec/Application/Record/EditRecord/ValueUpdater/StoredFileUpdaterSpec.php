<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditStoredFileValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\StoredFileUpdater;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpParser\Node\Arg;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StoredFileUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(StoredFileUpdater::class);
    }

    function it_only_supports_edit_upload_file_value_command(
        EditTextValueCommand $editTextValueCommand,
        EditStoredFileValueCommand $editStoredFileValueCommand
    ) {
        $this->supports($editTextValueCommand)->shouldReturn(false);
        $this->supports($editStoredFileValueCommand)->shouldReturn(true);
    }

    function it_edits_the_file_value_of_a_record(
        Record $record,
        Value $existingValue,
        FileData $existingFileData
    ) {
        $imageAttribute = $this->getAttribute();

        $command = new EditStoredFileValueCommand(
            $imageAttribute,
            'ecommerce',
            'de_DE',
            '/a/b/c/rillettes.png',
            'rillettes.png',
            2048,
            'image/png',
            'png'
        );

        $record->findValue(Argument::type(ValueKey::class))
            ->willReturn($existingValue);

        $existingValue->getData()
            ->willReturn($existingFileData);

        $existingFileData->getKey()->willReturn('/a/b/c/jambon.png');

        $record->setValue(Argument::type(Value::class))->shouldBeCalled();

        $this->__invoke($record, $command);
    }

    function it_sets_the_same_file_data_if_its_the_same_file(
        Record $record,
        Value $existingValue,
        FileData $existingFileData
    ) {
        $imageAttribute = $this->getAttribute();

        $command = new EditStoredFileValueCommand(
            $imageAttribute,
            'ecommerce',
            'de_DE',
            '/a/b/c/jambon.png',
            'jambon.png',
            2048,
            'image/png',
            'png'
        );

        $record->findValue(Argument::type(ValueKey::class))
            ->willReturn($existingValue);

        $existingValue->getData()
            ->willReturn($existingFileData);

        $existingFileData->getKey()->willReturn('/a/b/c/jambon.png');

        $value = Value::create(
            $command->attribute->getIdentifier(),
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleReference::createFromNormalized('de_DE'),
            $existingFileData->getWrappedObject()
        );

        $record->setValue($value)->shouldBeCalled();

        $this->__invoke($record, $command);
    }

    private function getAttribute(): ImageAttribute
    {
        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['fr_FR' => 'Image', 'en_US' => 'Image']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('120'),
            AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::VALID_EXTENSIONS)
        );

        return $imageAttribute;
    }
}
