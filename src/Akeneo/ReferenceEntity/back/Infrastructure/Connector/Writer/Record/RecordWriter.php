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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Writer\Record;

use Akeneo\ReferenceEntity\Application\Record\CreateAndEditRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordHandler;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\EditRecordHandler;
use Akeneo\ReferenceEntity\Domain\Exception\RecordAlreadyExistsError;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class RecordWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private CreateRecordHandler $createRecordHandler,
        private EditRecordHandler $editRecordHandler
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function write(array $createAndEditRecordCommands): void
    {
        if ([] === $createAndEditRecordCommands) {
            return;
        }

        foreach ($createAndEditRecordCommands as $createAndEditRecordCommand) {
            Assert::isInstanceOf($createAndEditRecordCommand, CreateAndEditRecordCommand::class);

            $isCreateRecordCommand = null !== $createAndEditRecordCommand->createRecordCommand;

            if ($isCreateRecordCommand) {
                try {
                    ($this->createRecordHandler)($createAndEditRecordCommand->createRecordCommand);
                } catch (RecordAlreadyExistsError) {
                    $isCreateRecordCommand = false;
                }
            }

            ($this->editRecordHandler)($createAndEditRecordCommand->editRecordCommand);

            $this->stepExecution?->incrementSummaryInfo(
                $isCreateRecordCommand ? 'create' : 'process'
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
