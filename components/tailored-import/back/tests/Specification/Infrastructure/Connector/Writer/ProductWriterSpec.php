<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Connector\Writer;

use Akeneo\Pim\Enrichment\Product\Api\Command\Exception\LegacyViolationsException;
use Akeneo\Pim\Enrichment\Product\Api\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\UpsertProductHandlerInterface;
use Akeneo\Platform\TailoredImport\Application\Common\ColumnCollection;
use Akeneo\Platform\TailoredImport\Application\Common\Row;
use Akeneo\Platform\TailoredImport\Infrastructure\Connector\RowPayload;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriterSpec extends ObjectBehavior
{
    private const DEFAULT_COLUMN_CONFIGURATION = [
        ['label' => 'Sku', 'uuid' => '25621f5a-504f-4893-8f0c-9f1b0076e53e', 'index' => 0],
        ['label' => 'Name', 'uuid' => '2d9e967a-5efa-4a31-a254-99f7c50a145c', 'index' => 1],
    ];

    private RowPayload $rowPayload;

    public function let(UpsertProductHandlerInterface $upsertProductHandler, StepExecution $stepExecution)
    {
        $this->rowPayload = new RowPayload(
            new Row([
                '25621f5a-504f-4893-8f0c-9f1b0076e53e' => 'ref1',
                '2d9e967a-5efa-4a31-a254-99f7c50a145c' => 'Produit 1',
            ]),
            ColumnCollection::createFromNormalized(self::DEFAULT_COLUMN_CONFIGURATION),
            0
        );
        $this->beConstructedWith($upsertProductHandler);
        $this->setStepExecution($stepExecution);
    }

    public function it_execute_upsert_command(UpsertProductHandlerInterface $upsertProductHandler)
    {
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: "identifier",valuesUserIntent: []);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $upsertProductHandler->__invoke($upsertProductCommand)->shouldBeCalled();
        $this->write([$this->rowPayload]);
    }

    public function it_should_catch_legacy_violation_exception(UpsertProductHandlerInterface $upsertProductHandler, StepExecution $stepExecution, ConstraintViolation $constraintViolation)
    {
        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn("error");
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: "identifier",valuesUserIntent: []);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $upsertProductHandler->__invoke($upsertProductCommand)->willThrow(new LegacyViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject(), $constraintViolation->getWrappedObject()])));
        $stepExecution->addWarning("error", [], new FileInvalidItem(['Sku' => 'ref1', 'Name' => 'Produit 1'], 0))->shouldBeCalled();
        $this->write([$this->rowPayload]);
    }

    public function it_should_catch_violation_exception(UpsertProductHandlerInterface $upsertProductHandler, StepExecution $stepExecution, ConstraintViolation $constraintViolation)
    {
        $constraintViolation->getParameters()->willReturn([]);
        $constraintViolation->getMessage()->willReturn("error");
        $upsertProductCommand = new UpsertProductCommand(userId: 1, productIdentifier: "identifier",valuesUserIntent: []);
        $this->rowPayload->setUpsertProductCommand($upsertProductCommand);
        $upsertProductHandler->__invoke($upsertProductCommand)->willThrow(new ViolationsException(new ConstraintViolationList([$constraintViolation->getWrappedObject(), $constraintViolation->getWrappedObject()])));
        $stepExecution->addWarning("error", [], new FileInvalidItem(['Sku' => 'ref1', 'Name' => 'Produit 1'], 0))->shouldBeCalled();
        $this->write([$this->rowPayload]);
    }
}