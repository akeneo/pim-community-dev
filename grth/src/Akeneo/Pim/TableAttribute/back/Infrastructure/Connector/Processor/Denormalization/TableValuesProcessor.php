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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Denormalization;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TableAttribute\Domain\Value\Row;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\DTO\TableRow;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Webmozart\Assert\Assert;

final class TableValuesProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private IdentifiableObjectRepositoryInterface $repository;
    private GetAttributes $getAttributes;
    private StepExecution $stepExecution;

    public function __construct(IdentifiableObjectRepositoryInterface $repository, GetAttributes $getAttributes)
    {
        $this->repository = $repository;
        $this->getAttributes = $getAttributes;
    }

    /**
     * {@inheritDoc}
     */
    public function process($item): TableRow
    {
        Assert::notNull($this->stepExecution);
        if (null === $this->repository->findOneByIdentifier($item['entity'])) {
            $this->skipWithMessage($item, \sprintf("The '%s' product or product model is unknown.", $item['entity']));
        }

        $attribute = $this->getAttributes->forCode($item['attribute_code']);
        if (null === $attribute) {
            $this->skipWithMessage($item, \sprintf("The '%s' attribute is unknown", $item['attribute_code']));
        }

        if (AttributeTypes::TABLE !== $attribute->type()) {
            $this->skipWithMessage(
                $item,
                \sprintf("The '%s' attribute should be a table attribute", $item['attribute_code'])
            );
        }

        return new TableRow(
            $item['entity'],
            $item['attribute_code'],
            $item['locale'],
            $item['scope'],
            Row::fromNormalized($item['row_values'])
        );
    }

    /**
     * @throws InvalidItemException
     */
    private function skipWithMessage(array $item, string $message): void
    {
        $this->stepExecution->incrementSummaryInfo('skip');

        throw new InvalidItemException($message, new DataInvalidItem($item));
    }

    /**
     * {@inheritDoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
