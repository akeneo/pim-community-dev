<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Reader\Database;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\CountSelectOptions;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetSelectOptionDetails;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

final class SelectOptionDetailsReader implements ItemReaderInterface, TrackableItemReaderInterface, InitializableInterface, StepExecutionAwareInterface
{
    private CountSelectOptions $countSelectOptions;
    private GetSelectOptionDetails $getSelectOptionDetails;
    private ?StepExecution $stepExecution = null;
    private ?\Iterator $results = null;

    public function __construct(CountSelectOptions $countSelectOptions, GetSelectOptionDetails $getSelectOptionDetails)
    {
        $this->countSelectOptions = $countSelectOptions;
        $this->getSelectOptionDetails = $getSelectOptionDetails;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function initialize()
    {
        $this->results = ($this->getSelectOptionDetails)();
    }

    public function read(): ?SelectOptionDetails
    {
        $optionDetails = $this->results->current();
        if (null !== $optionDetails) {
            $this->results->next();
            $this->stepExecution->incrementSummaryInfo('read');
        }

        return $optionDetails;
    }

    public function totalItems(): int
    {
        return $this->countSelectOptions->all();
    }
}
