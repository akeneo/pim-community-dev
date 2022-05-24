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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
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
        ['label' => 'Categories', 'uuid' => 'd8ab5017-3338-400b-95af-9dc16500ebf8', 'index' => 2],
        ['label' => 'Family', 'uuid' => '1f1360fa-641d-494e-a0ef-460cfd4a7033', 'index' => 3],
    ];

    private RowPayload $rowPayload;

    public function let(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        EventDispatcher $eventDispatcher
    ) {
        $this->rowPayload = new RowPayload(
            new Row([
                '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'ref1',
                '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'Produit 1',
                'd8ab5017-3338-400b-95af-9dc16500ebf8' => 'clothes, shoes',
                '1f1360fa-641d-494e-a0ef-460cfd4a7033' => 'a_family',
            ]),
            ColumnCollection::createFromNormalized(self::DEFAULT_COLUMN_CONFIGURATION),
            0
        );
        $this->beConstructedWith($messageBus, $eventDispatcher);

        $stepExecution->getJobParameters()->willReturn(new JobParameters(['error_action' => 'skip_product']));
        $stepExecution->getSummaryInfo('item_position', 0)->willReturn(1);
        $stepExecution->getSummaryInfo('create', 0)->willReturn(1);
        $stepExecution->getSummaryInfo('process', 0)->willReturn(0);
        $stepExecution->getSummaryInfo('skip', 0)->willReturn(0);
        $stepExecution->getSummaryInfo('skipped_no_diff', 0)->willReturn(0);
        $stepExecution->incrementSummaryInfo('skipped_no_diff', 0)->shouldBeCalled();
        $this->setStepExecution($stepExecution);
    }

    public function it_executes_an_upsert_command_without_user_intent(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
    ) {
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'ref1', valueUserIntents: []);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);

        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));

        $this->write([$this->rowPayload]);
    }

    public function it_executes_an_upsert_command_with_value_user_intent(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
    ) {
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'ref1', valueUserIntents: [
            new SetTextValue('name', null, null, 'Produit 1'),
        ]);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);

        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));

        $this->write([$this->rowPayload]);
    }

    public function it_executes_an_upsert_command_with_category_user_intent(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
    ) {
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'ref1', categoryUserIntent: new AddCategories(['clothes', 'shoes']));
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);

        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));

        $this->write([$this->rowPayload]);
    }

    public function it_executes_an_upsert_command_with_family_user_intent(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
    ) {
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'ref1', familyUserIntent: new SetFamily('a_family'));
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);

        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));

        $this->write([$this->rowPayload]);
    }

    public function it_should_skip_product_when_legacy_violation_is_thrown(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        ConstraintViolation $constraintViolation,
    ) {
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'ref1', valueUserIntents: [
            new SetTextValue('name', null, null, 'Produit 1'),
        ]);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn('legacy error');
        $constraintViolation->__toString()->willReturn('legacy error');

        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willThrow(new LegacyViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject()])));
        $stepExecution->addWarning('legacy error', [], new FileInvalidItem([
            'Sku' => 'ref1',
            'Name' => 'Produit 1',
            'Categories' => 'clothes, shoes',
            'Family' => 'a_family',
        ], 0))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledOnce();
        $stepExecution->getSummaryInfo('create', 0)->willReturn(0);
        $stepExecution->getSummaryInfo('skip', 0)->willReturn(1);

        $this->write([$this->rowPayload]);
    }

    public function it_should_skip_product_when_empty_property_path_violation_is_thrown(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        ConstraintViolation $constraintViolation,
    ) {
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'ref1', valueUserIntents: [
            new SetTextValue('name', null, null, 'Produit 1'),
        ]);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn('error');
        $constraintViolation->__toString()->willReturn('error');
        $constraintViolation->getPropertyPath()->willReturn('');

        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willThrow(new ViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject()])));
        $stepExecution->addWarning('error', [], new FileInvalidItem([
            'Sku' => 'ref1',
            'Name' => 'Produit 1',
            'Categories' => 'clothes, shoes',
            'Family' => 'a_family',
        ], 0))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledOnce();
        $stepExecution->getSummaryInfo('create', 0)->willReturn(0);
        $stepExecution->getSummaryInfo('skip', 0)->willReturn(1);

        $this->write([$this->rowPayload]);
    }

    public function it_should_skip_value_and_retry_when_violation_is_thrown(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        ConstraintViolation $constraintViolation,
    ) {
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'ref1', valueUserIntents: [
            new SetTextValue('name', null, null, 'Produit 1'),
            new SetTextValue('reference', null, null, 'ref1'),
        ]);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $stepExecution->getJobParameters()->willReturn(new JobParameters(['error_action' => 'skip_value']));
        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn('error');
        $constraintViolation->__toString()->willReturn('error');
        $constraintViolation->getPropertyPath()->willReturn('valueUserIntents[0]');

        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willThrow(new ViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject()])));
        $stepExecution->addWarning('error', [], new FileInvalidItem([
            'Sku' => 'ref1',
            'Name' => 'Produit 1',
            'Categories' => 'clothes, shoes',
            'Family' => 'a_family',
        ], 0))->shouldBeCalled();

        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'ref1', valueUserIntents: [
            new SetTextValue('reference', null, null, 'ref1'),
        ]);
        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));

        $this->write([$this->rowPayload]);
    }

    public function it_should_skip_category_and_retry_when_violation_is_thrown(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        ConstraintViolation $constraintViolation,
    ) {
        $upsertProductCommand = new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'ref1',
            familyUserIntent: new SetFamily('a_family'),
            categoryUserIntent: new SetCategories(['clothes', 'shoes']),
            valueUserIntents: [new SetTextValue('name', null, null, 'Produit 1')],
        );

        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $stepExecution->getJobParameters()->willReturn(new JobParameters(['error_action' => 'skip_value']));
        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn('error');
        $constraintViolation->__toString()->willReturn('error');
        $constraintViolation->getPropertyPath()->willReturn('categoryUserIntent');

        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willThrow(new ViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject()])));
        $stepExecution->addWarning('error', [], new FileInvalidItem([
            'Sku' => 'ref1',
            'Name' => 'Produit 1',
            'Categories' => 'clothes, shoes',
            'Family' => 'a_family',
        ], 0))->shouldBeCalled();

        $upsertProductCommand = new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'ref1',
            familyUserIntent: new SetFamily('a_family'),
            valueUserIntents: [new SetTextValue('name', null, null, 'Produit 1')],
        );
        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));

        $this->write([$this->rowPayload]);
    }

    public function it_should_skip_family_and_retry_when_violation_is_thrown(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        ConstraintViolation $constraintViolation,
    ) {
        $upsertProductCommand = new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'ref1',
            familyUserIntent: new SetFamily('a_family'),
            categoryUserIntent: new SetCategories(['clothes', 'shoes']),
            valueUserIntents: [new SetTextValue('name', null, null, 'Produit 1')],
        );

        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $stepExecution->getJobParameters()->willReturn(new JobParameters(['error_action' => 'skip_value']));
        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn('error');
        $constraintViolation->__toString()->willReturn('error');
        $constraintViolation->getPropertyPath()->willReturn('familyUserIntent');

        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willThrow(new ViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject()])));
        $stepExecution->addWarning('error', [], new FileInvalidItem([
            'Sku' => 'ref1',
            'Name' => 'Produit 1',
            'Categories' => 'clothes, shoes',
            'Family' => 'a_family',
        ], 0))->shouldBeCalled();

        $upsertProductCommand = new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'ref1',
            categoryUserIntent: new SetCategories(['clothes', 'shoes']),
            valueUserIntents: [new SetTextValue('name', null, null, 'Produit 1')],
        );
        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));

        $this->write([$this->rowPayload]);
    }

    public function it_should_skip_product_when_violations_are_thrown_and_there_is_no_user_intent_remaining(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        ConstraintViolation $valueConstraintViolation,
        ConstraintViolation $categoryConstraintViolation,
        ConstraintViolation $familyConstraintViolation,
    ) {
        $upsertProductCommand = new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'ref1',
            familyUserIntent: new SetFamily('a_family'),
            categoryUserIntent: new SetCategories(['clothes', 'shoes']),
            valueUserIntents: [new SetTextValue('name', null, null, 'Produit 1')],
        );

        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $stepExecution->getJobParameters()->willReturn(new JobParameters(['error_action' => 'skip_value']));

        $valueConstraintViolation->getParameters()->willReturn([]);
        $valueConstraintViolation->getMessage()->willReturn('value error');
        $valueConstraintViolation->__toString()->willReturn('value error');
        $valueConstraintViolation->getPropertyPath()->willReturn('valueUserIntents[0]');

        $categoryConstraintViolation->getParameters()->willReturn([]);
        $categoryConstraintViolation->getMessage()->willReturn('category error');
        $categoryConstraintViolation->__toString()->willReturn('category error');
        $categoryConstraintViolation->getPropertyPath()->willReturn('categoryUserIntent');

        $familyConstraintViolation->getParameters()->willReturn([]);
        $familyConstraintViolation->getMessage()->willReturn('family error');
        $familyConstraintViolation->__toString()->willReturn('family error');
        $familyConstraintViolation->getPropertyPath()->willReturn('familyUserIntent');

        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willThrow(new ViolationsException(new ConstraintViolationList([
            $valueConstraintViolation->getWrappedObject(),
            $categoryConstraintViolation->getWrappedObject(),
            $familyConstraintViolation->getWrappedObject(),
        ])));

        $fileInvalidItem = new FileInvalidItem([
            'Sku' => 'ref1',
            'Name' => 'Produit 1',
            'Categories' => 'clothes, shoes',
            'Family' => 'a_family'], 0
        );

        $stepExecution->addWarning('value error', [], $fileInvalidItem)->shouldBeCalled();
        $stepExecution->addWarning('category error', [], $fileInvalidItem)->shouldBeCalled();
        $stepExecution->addWarning('family error', [], $fileInvalidItem)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledOnce();
        $stepExecution->getSummaryInfo('create', 0)->willReturn(0);
        $stepExecution->getSummaryInfo('skip', 0)->willReturn(1);

        $messageBus->dispatch()->shouldNotBeCalled();

        $this->write([$this->rowPayload]);
    }

    public function it_should_skip_product_when_no_user_intent_have_been_cleaned(
        MessageBusInterface $messageBus,
        StepExecution $stepExecution,
        ConstraintViolation $constraintViolation
    ) {
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: 'ref1', valueUserIntents: [
            new SetTextValue('name', null, null, 'Produit 1'),
            new SetTextValue('reference', null, null, 'ref1'),
        ]);

        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $stepExecution->getJobParameters()->willReturn(new JobParameters(['error_action' => 'skip_value']));
        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn('unknown error');
        $constraintViolation->__toString()->willReturn('unknown error');
        $constraintViolation->getPropertyPath()->willReturn('unknown');

        $messageBus->dispatch($upsertProductCommand)->shouldBeCalled()->willThrow(new ViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject()])));
        $stepExecution->addWarning('unknown error', [], new FileInvalidItem(['Sku' => 'ref1', 'Name' => 'Produit 1', 'Categories' => 'clothes, shoes', 'Family' => 'a_family'], 0))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledOnce();
        $stepExecution->getSummaryInfo('create', 0)->willReturn(0);
        $stepExecution->getSummaryInfo('skip', 0)->willReturn(1);

        $this->write([$this->rowPayload]);
    }
}
