<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Connector\Writer;

use Akeneo\Pim\Enrichment\Product\API\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Platform\TailoredImport\Domain\Model\ColumnCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use Akeneo\Platform\TailoredImport\Infrastructure\Connector\RowPayload;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ProductWriterSpec extends ObjectBehavior
{
    private const DEFAULT_COLUMN_CONFIGURATION = [
        ['label' => 'Sku', 'uuid' => '25621f5a-504f-4893-8f0c-9f1b0076e53e', 'index' => 0],
        ['label' => 'Name', 'uuid' => '2d9e967a-5efa-4a31-a254-99f7c50a145c', 'index' => 1],
    ];

    private RowPayload $rowPayload;

    public function let(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        EventDispatcher $eventDispatcher
    ) {
        $stepExecution->getSummaryInfo('item_position', 0)->willReturn(4);
        $stepExecution->getSummaryInfo('create', 0)->willReturn(1);
        $stepExecution->getSummaryInfo('process', 0)->willReturn(2);
        $stepExecution->getSummaryInfo('skip', 0)->willReturn(1);
        $stepExecution->getSummaryInfo('skipped_no_diff', 0)->willReturn(0);

        $stepExecution->incrementSummaryInfo('skipped_no_diff', 0)->shouldBeCalled();

        $this->rowPayload = new RowPayload(
            new Row([
                '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'ref1',
                '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'Produit 1',
            ]),
            ColumnCollection::createFromNormalized(self::DEFAULT_COLUMN_CONFIGURATION),
            0
        );
        $this->beConstructedWith($messageBus, $eventDispatcher);
        $this->setStepExecution($stepExecution);
    }

    public function it_execute_upsert_command(MessageBusInterface $messageBus)
    {
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'identifier', valueUserIntents: []);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));
        $this->write([$this->rowPayload]);
    }

    public function it_should_catch_legacy_violation_exception(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        ConstraintViolation $constraintViolation
    ) {
        $stepExecution->getJobParameters()->willReturn(new JobParameters(['error_action' => 'skip_product']));

        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn("error");
        $constraintViolation->__toString()->willReturn("error");

        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: "identifier", valueUserIntents: []);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $messageBus->dispatch($upsertProductCommand)->willThrow(new LegacyViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject()])));
        $stepExecution->addWarning('error', [], new FileInvalidItem(['Sku' => 'ref1', 'Name' => 'Produit 1'], 0))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledOnce();

        $this->write([$this->rowPayload]);
    }

    public function it_should_catch_violation_exception(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        ConstraintViolation $constraintViolation
    ) {
        $stepExecution->getJobParameters()->willReturn(new JobParameters(['error_action' => 'skip_product']));

        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn('error');
        $constraintViolation->__toString()->willReturn('error');

        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: "identifier", valueUserIntents: []);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $messageBus->dispatch($upsertProductCommand)->willThrow(new ViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject()])));
        $stepExecution->addWarning('error', [], new FileInvalidItem(['Sku' => 'ref1', 'Name' => 'Produit 1'], 0))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledOnce();

        $this->write([$this->rowPayload]);
    }

    public function it_should_skip_value_on_violations(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        ConstraintViolation $constraintViolation
    ) {
        $stepExecution->getJobParameters()->willReturn(new JobParameters(['error_action' => 'skip_value']));

        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn('error');
        $constraintViolation->__toString()->willReturn('error');
        $constraintViolation->getPropertyPath()->willReturn('valueUserIntents[1]');

        $valueUserIntents = [
            new SetTextValue('name', null, null, value: 'A name'),
            new SetTextValue('description', null, null, 'A description with error'),
        ];

        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'identifier', valueUserIntents: $valueUserIntents);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);

        $messageBus->dispatch($upsertProductCommand)->willThrow(
            new ViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject()]))
        );
        $stepExecution->addWarning('error', [], new FileInvalidItem(['Sku' => 'ref1', 'Name' => 'Produit 1'], 0))->shouldBeCalled();

        unset($valueUserIntents[1]);
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'identifier', valueUserIntents: $valueUserIntents);
        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));

        $this->write([$this->rowPayload]);
    }

    public function it_should_not_retry_when_value_still_the_same(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        ConstraintViolation $constraintViolation
    ) {
        $stepExecution->getJobParameters()->willReturn(new JobParameters(['error_action' => 'skip_value']));

        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn('error');
        $constraintViolation->__toString()->willReturn('error');
        $constraintViolation->getPropertyPath()->willReturn('wrong_property_path');

        $valueUserIntents = [
            new SetTextValue('name', null, null, value: 'A name'),
            new SetTextValue('description', null, null, 'A description'),
        ];

        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'identifier', valueUserIntents: $valueUserIntents);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);

        $messageBus->dispatch($upsertProductCommand)
            ->willThrow(new ViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject()])))
            ->shouldBeCalledOnce();
        $stepExecution->addWarning('error', [], new FileInvalidItem(['Sku' => 'ref1', 'Name' => 'Produit 1'], 0))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledOnce();

        $this->write([$this->rowPayload]);
    }
}
