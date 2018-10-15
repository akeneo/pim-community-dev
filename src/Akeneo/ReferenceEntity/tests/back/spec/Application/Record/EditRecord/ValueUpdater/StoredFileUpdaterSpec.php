<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditStoredFileValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\StoredFileUpdater;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
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

    function it_only_supports_edit_upload_file_value_command()
    {
        $this->supports(new EditTextValueCommand())->shouldReturn(false);
        $this->supports(new EditStoredFileValueCommand())->shouldReturn(true);
    }

    function it_edits_the_file_value_of_a_record(
        ImageAttribute $imageAttribute,
        EditStoredFileValueCommand $command,
        Record $record,
        Value $existingValue,
        FileData $existingFileData
    ) {
        $imageAttribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('picture'));

        $command->attribute = $imageAttribute;
        $command->channel = 'ecommerce';
        $command->locale = 'de_DE';
        $command->filePath = '/a/b/c/rillettes.png';
        $command->originalFilename = 'rillettes.png';
        $command->size = 2048;
        $command->mimeType = 'image/png';
        $command->extension = 'png';

        $record->findValue(Argument::type(ValueKey::class))
            ->willReturn($existingValue);

        $existingValue->getData()
            ->willReturn($existingFileData);

        $existingFileData->getKey()->willReturn('/a/b/c/jambon.png');

        $record->setValue(Argument::type(Value::class))->shouldBeCalled();

        $this->__invoke($record, $command);
    }

    function it_sets_the_same_file_data_if_its_the_same_file(
        ImageAttribute $imageAttribute,
        EditStoredFileValueCommand $command,
        Record $record,
        Value $existingValue,
        FileData $existingFileData
    ) {
        $imageAttribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('picture'));

        $command->attribute = $imageAttribute;
        $command->channel = 'ecommerce';
        $command->locale = 'de_DE';
        $command->filePath = '/a/b/c/jambon.png';
        $command->originalFilename = 'jambon.png';
        $command->size = 2048;
        $command->mimeType = 'image/png';
        $command->extension = 'png';

        $record->findValue(Argument::type(ValueKey::class))
            ->willReturn($existingValue);

        $existingValue->getData()
            ->willReturn($existingFileData);

        $existingFileData->getKey()->willReturn('/a/b/c/jambon.png');

        $value = Value::create(
            $command->attribute->getIdentifier(),
            ChannelReference::createfromNormalized('ecommerce'),
            LocaleReference::createfromNormalized('de_DE'),
            $existingFileData->getWrappedObject()
        );

        $record->setValue($value)->shouldBeCalled();

        $this->__invoke($record, $command);
    }
}
