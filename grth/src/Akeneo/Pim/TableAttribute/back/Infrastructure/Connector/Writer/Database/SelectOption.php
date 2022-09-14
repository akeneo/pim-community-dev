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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\Database;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Webmozart\Assert\Assert;

final class SelectOption implements ItemWriterInterface, StepExecutionAwareInterface
{
    private SelectOptionCollectionRepository $selectOptionCollectionRepository;
    private StepExecution $stepExecution;

    public function __construct(SelectOptionCollectionRepository $selectOptionCollectionRepository)
    {
        $this->selectOptionCollectionRepository = $selectOptionCollectionRepository;
    }

    public function write(array $items): void
    {
        Assert::allIsInstanceOf($items, SelectOptionDetails::class);

        $optionsByAttributeAndColumn = [];
        /** @var SelectOptionDetails $item */
        foreach ($items as $item) {
            $optionsByAttributeAndColumn[$item->attributeCode()][$item->columnCode()][] = [
                'code' => $item->optionCode(),
                'labels' => $item->labels(),
            ];
        }

        foreach ($optionsByAttributeAndColumn as $attributeCode => $optionsByColumn) {
            foreach ($optionsByColumn as $columnCode => $options) {
                $this->selectOptionCollectionRepository->upsert(
                    $attributeCode,
                    ColumnCode::fromString($columnCode),
                    SelectOptionCollection::fromNormalized($options)
                );
                $this->stepExecution->incrementSummaryInfo('update', \count($options));
            }
        }
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
