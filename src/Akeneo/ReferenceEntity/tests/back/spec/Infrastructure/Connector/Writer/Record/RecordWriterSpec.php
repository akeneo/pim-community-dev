<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Writer\Record;

use Akeneo\ReferenceEntity\Application\Record\CreateAndEditRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordHandler;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\EditRecordHandler;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Writer\Record\RecordWriter;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class RecordWriterSpec extends ObjectBehavior
{
    function let(CreateRecordHandler $createRecordHandler, EditRecordHandler $editRecordHandler)
    {
        $this->beConstructedWith($createRecordHandler, $editRecordHandler);
        $stepExecution = new StepExecution('name', new JobExecution());
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(RecordWriter::class);
    }

    function it_is_an_item_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    function it_handles_a_set_of_commands_to_create_and_edit_records(
        CreateRecordHandler $createRecordHandler,
        EditRecordHandler $editRecordHandler
    ) {
        $commandToCreate = $this->aCommandToCreate();
        $createRecordHandler->__invoke($commandToCreate->createRecordCommand)->shouldBeCalled();
        $editRecordHandler->__invoke($commandToCreate->editRecordCommand)->shouldBeCalled();

        $commandToEdit = $this->aCommandToEdit();
        $editRecordHandler->__invoke($commandToEdit->editRecordCommand)->shouldBeCalled();

        $this->write([$commandToCreate, $commandToEdit]);
    }

    function it_fails_when_a_command_is_not_a_create_and_edit_record_command()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('write', [[new \StdClass]]);
    }

    function aCommandToCreate(): CreateAndEditRecordCommand
    {
        return new CreateAndEditRecordCommand(
            new CreateRecordCommand('ref1', 'rec1', []),
            new EditRecordCommand('ref1', 'rec1', [], null, [])
        );
    }

    function aCommandToEdit(): CreateAndEditRecordCommand
    {
        return new CreateAndEditRecordCommand(
            null,
            new EditRecordCommand('ref1', 'rec1', [], null, [])
        );
    }
}
