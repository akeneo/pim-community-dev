<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditUploadedFileValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\UploadedFileUpdater;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UploadedFileUpdaterSpec extends ObjectBehavior
{
    function let(FileStorerInterface $storer)
    {
        $this->beConstructedWith($storer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UploadedFileUpdater::class);
    }

    function it_only_supports_edit_upload_file_value_command()
    {
        $this->supports(new EditTextValueCommand())->shouldReturn(false);
        $this->supports(new EditUploadedFileValueCommand())->shouldReturn(true);
    }

    function it_edits_the_file_value_of_a_record(
        FileStorerInterface $storer,
        ImageAttribute $imageAttribute,
        EditUploadedFileValueCommand $command,
        FileInfo $fileInfo,
        Record $record
    ) {
        $imageAttribute->getIdentifier()->willReturn(AttributeIdentifier::fromString('picture'));

        $command->attribute = $imageAttribute;
        $command->channel = 'ecommerce';
        $command->locale = 'de_DE';
        $command->filePath = '/tmp/jambon.png';
        $command->originalFilename = 'jambon.png';

        $fileInfo->getKey()->willReturn('a/b/c/jambon.png');
        $fileInfo->getOriginalFilename()->willReturn('jambon.png');
        $fileInfo->getSize()->willReturn(1024);
        $fileInfo->getMimeType()->willReturn('image/png');
        $fileInfo->getExtension()->willReturn('png');

        $storer->store(Argument::type(\SplFileInfo::class), 'catalogStorage')
            ->willReturn($fileInfo);

        $value = Value::create(
            $command->attribute->getIdentifier(),
            ChannelReference::createfromNormalized('ecommerce'),
            LocaleReference::createfromNormalized('de_DE'),
            FileData::createFromNormalize([
                'filePath' => 'a/b/c/jambon.png',
                'originalFilename' => 'jambon.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png'
            ])
        );

        $record->setValue($value)->shouldBeCalled();

        $this->__invoke($record, $command);
    }

    function it_throws_an_exception_if_it_does_not_support_the_command(Record $record)
    {
        $wrongCommand = new EditTextValueCommand();
        $this->supports($wrongCommand)->shouldReturn(false);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$record, $wrongCommand]);
    }
}
