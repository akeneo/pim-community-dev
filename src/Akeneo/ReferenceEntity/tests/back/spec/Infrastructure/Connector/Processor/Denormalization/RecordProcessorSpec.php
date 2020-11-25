<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Processor\Denormalization;

use Akeneo\ReferenceEntity\Application\Record\CreateAndEditRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\Connector\EditRecordCommandFactory;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindImageAttributeCodesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\MediaFile\UploadMediaFileAction;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Processor\Denormalization\RecordProcessor;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\FileStorage\Exception\InvalidFile;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RecordProcessorSpec extends ObjectBehavior
{
    function let(
        EditRecordCommandFactory $editRecordCommandFactory,
        RecordExistsInterface $recordExists,
        ValidatorInterface $validator,
        FindImageAttributeCodesInterface $findImageAttributeCodes,
        FileStorerInterface $fileStorer
    ) {
        $this->beConstructedWith(
            $editRecordCommandFactory,
            $recordExists,
            $validator,
            $findImageAttributeCodes,
            $fileStorer
        );

        $findImageAttributeCodes->find(ReferenceEntityIdentifier::fromString('brand'))
            ->willReturn(['profile', 'picture']);

        $stepExecution = new StepExecution('name', new JobExecution());
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(RecordProcessor::class);
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_processes_an_item_by_returning_a_command_that_creates_and_edits(
        EditRecordCommandFactory $editRecordCommandFactory,
        RecordExistsInterface $recordExists,
        ValidatorInterface $validator,
        FileStorerInterface $fileStorer,
        EditRecordCommand $editRecordCommand
    ) {
        $item = $this->anItem();
        $this->imageIsStored($fileStorer);
        $this->theItemIsValid($validator, $editRecordCommandFactory, $editRecordCommand);
        $this->theRecordDoesNotExist($recordExists);

        $createAndEditRecordCommand = $this->process($item);

        $createAndEditRecordCommand->shouldBeAnInstanceOf(CreateAndEditRecordCommand::class);
        $createAndEditRecordCommand->createRecordCommand->shouldBeAnInstanceOf(CreateRecordCommand::class);
        $createAndEditRecordCommand->editRecordCommand->shouldBeAnInstanceOf(EditRecordCommand::class);
    }

    function it_processes_an_item_by_returning_a_command_that_only_edits(
        EditRecordCommandFactory $editRecordCommandFactory,
        RecordExistsInterface $recordExists,
        ValidatorInterface $validator,
        FileStorerInterface $fileStorer,
        EditRecordCommand $editRecordCommand
    ) {
        $item = $this->anItem();
        $this->imageIsStored($fileStorer);
        $this->theItemIsValid($validator, $editRecordCommandFactory, $editRecordCommand);
        $this->theRecordAlreadyExists($recordExists);

        $createAndEditRecordCommand = $this->process($item);

        $createAndEditRecordCommand->shouldBeAnInstanceOf(CreateAndEditRecordCommand::class);
        $createAndEditRecordCommand->createRecordCommand->shouldBeNull();
        $createAndEditRecordCommand->editRecordCommand->shouldBeAnInstanceOf(EditRecordCommand::class);
    }

    function it_fails_at_image_storage(FileStorerInterface $fileStorer)
    {
        $item = $this->anItem();
        $this->exceptionIsThrownDuringStorage($fileStorer);

        $this->shouldThrow(InvalidItemException::class)->during('process', [$item]);
    }

    function it_fails_at_create_validation(
        RecordExistsInterface $recordExists,
        ValidatorInterface $validator,
        FileStorerInterface $fileStorer
    ) {
        $item = $this->anItem();
        $this->imageIsStored($fileStorer);
        $this->theNewItemIsNotValid($validator);
        $this->theRecordDoesNotExist($recordExists);

        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('process', [$item]);
    }

    function it_fails_at_edit_validation(
        EditRecordCommandFactory $editRecordCommandFactory,
        RecordExistsInterface $recordExists,
        ValidatorInterface $validator,
        FileStorerInterface $fileStorer,
        EditRecordCommand $editRecordCommand
    ) {
        $item = $this->anItem();
        $this->imageIsStored($fileStorer);
        $this->theUpdatedItemIsNotValid($validator, $editRecordCommandFactory, $editRecordCommand);
        $this->theRecordAlreadyExists($recordExists);

        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('process', [$item]);
    }

    private function anItem(): array
    {
        return [
            'reference_entity_identifier' => 'brand',
            'code' => 'rec1',
            'values' => [
                'name' => [['locale' => null, 'channel' => null, 'data' => 'The name']],
                'picture' => [
                    ['locale' => 'en_US', 'channel' => null, 'data' => 'path/test.jpg'],
                    ['locale' => 'fr_FR', 'channel' => null, 'data' => ''],
                ],
            ],
        ];
    }

    private function itemAfterImageStoring(): array
    {
        return [
            'reference_entity_identifier' => 'brand',
            'code' => 'rec1',
            'values' => [
                'name' => [['locale' => null, 'channel' => null, 'data' => 'The name']],
                'picture' => [
                    ['locale' => 'en_US', 'channel' => null, 'data' => 'the key'],
                    ['locale' => 'fr_FR', 'channel' => null, 'data' => ''],
                ],
            ],
        ];
    }

    private function imageIsStored(FileStorerInterface $fileStorer): void
    {
        $fileInfo = new FileInfo();
        $fileInfo->setKey('the key');
        $fileStorer->store(new \SplFileInfo('path/test.jpg'), UploadMediaFileAction::FILE_STORAGE_ALIAS)
            ->willReturn($fileInfo);
    }

    private function exceptionIsThrownDuringStorage(FileStorerInterface $fileStorer): void
    {
        $fileStorer->store(new \SplFileInfo('path/test.jpg'), UploadMediaFileAction::FILE_STORAGE_ALIAS)
            ->willThrow(new InvalidFile());;
    }

    private function theItemIsValid(
        ValidatorInterface $validator,
        EditRecordCommandFactory $editRecordCommandFactory,
        EditRecordCommand $editRecordCommand
    ): void {
        $item = $this->itemAfterImageStoring();

        $createRecordCommand = new CreateRecordCommand('brand', 'rec1', []);
        $validator->validate($createRecordCommand)->willReturn(new ConstraintViolationList());

        $editRecordCommandFactory->create(ReferenceEntityIdentifier::fromString('brand'), $item)
            ->willReturn($editRecordCommand);
        $validator->validate($editRecordCommand)->willReturn(new ConstraintViolationList());
    }

    private function theNewItemIsNotValid(ValidatorInterface $validator): void
    {
        $createRecordCommand = new CreateRecordCommand('brand', 'rec1', []);
        $validator->validate($createRecordCommand)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('not valid', '', [], null, null, null),
        ]));
    }

    private function theUpdatedItemIsNotValid(
        ValidatorInterface $validator,
        EditRecordCommandFactory $editRecordCommandFactory,
        EditRecordCommand $editRecordCommand
    ): void {
        $item = $this->itemAfterImageStoring();

        $createRecordCommand = new CreateRecordCommand('brand', 'rec1', []);
        $validator->validate($createRecordCommand)->willReturn(new ConstraintViolationList());

        $editRecordCommandFactory->create(ReferenceEntityIdentifier::fromString('brand'), $item)
            ->willReturn($editRecordCommand);
        $validator->validate($editRecordCommand)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('not valid', '', [], null, null, null),
        ]));
    }

    private function theRecordDoesNotExist(RecordExistsInterface $recordExists): void
    {
        $recordExists->withReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('rec1')
        )->willReturn(false);
    }

    private function theRecordAlreadyExists(RecordExistsInterface $recordExists): void
    {
        $recordExists->withReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('rec1')
        )->willReturn(true);
    }
}
