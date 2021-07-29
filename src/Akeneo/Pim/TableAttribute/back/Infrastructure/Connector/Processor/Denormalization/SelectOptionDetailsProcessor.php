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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\LabelCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\CountSelectOptions;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class SelectOptionDetailsProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private SelectOptionCollectionRepository $selectOptionCollectionRepository;
    private ValidatorInterface $validator;
    private CountSelectOptions $countSelectOptions;
    private ?StepExecution $stepExecution = null;
    private array $optionCountByAttributeAndColumn = [];

    public function __construct(
        SelectOptionCollectionRepository $selectOptionCollectionRepository,
        ValidatorInterface $validator,
        CountSelectOptions $countSelectOptions
    ) {
        $this->selectOptionCollectionRepository = $selectOptionCollectionRepository;
        $this->validator = $validator;
        $this->countSelectOptions = $countSelectOptions;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function process($item): SelectOptionDetails
    {
        Assert::keyExists($item, 'attribute');
        Assert::stringNotEmpty($item['attribute']);
        Assert::keyExists($item, 'column');
        Assert::stringNotEmpty($item['column']);
        Assert::keyExists($item, 'code');
        Assert::stringNotEmpty($item['code']);

        Assert::isArray($item['labels'] ?? []);

        $labels = LabelCollection::fromNormalized($item['labels'] ?? []);
        $options = $this->selectOptionCollectionRepository->getByColumn(
            $item['attribute'],
            ColumnCode::fromString($item['column'])
        );

        $option = $options->getByCode($item['code']);
        $isCreation = null === $option;
        if ($isCreation) {
            $this->checkMaxOptionCountIsNotReached($item);
        } else {
            $labels = $option->labels()->merge($labels);
        }
        $normalizedLabels = $labels->normalize();

        $details = new SelectOptionDetails(
            $item['attribute'],
            $item['column'],
            $item['code'],
            \is_object($normalizedLabels) ? [] : $normalizedLabels
        );
        $violations = $this->validator->validate($details);
        if (0 < $violations->count()) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        if ($isCreation) {
            $this->incrementCount($item['attribute'], $item['column']);
        }

        return $details;
    }

    private function skipItemWithConstraintViolations(
        array $item,
        ConstraintViolationListInterface $violations
    ): void {
        $itemPosition = 0;
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
            $itemPosition = $this->stepExecution->getSummaryInfo('item_position');
        }

        throw new InvalidItemFromViolationsException($violations, new FileInvalidItem($item, $itemPosition));
    }

    /**
     * @throws InvalidItemException
     */
    private function checkMaxOptionCountIsNotReached($item): void
    {
        if ($this->getCount($item['attribute'], $item['column']) < SelectOptionCollection::MAX_OPTIONS) {
            return;
        };


        $itemPosition = 0;
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
            $itemPosition = $this->stepExecution->getSummaryInfo('item_position');
        }

        throw new InvalidItemException('TODO too much options', new FileInvalidItem($item, $itemPosition));
    }

    private function getCount(string $attributeCode, string $columnCode): int
    {
        if (null === ($this->optionCountByAttributeAndColumn[$attributeCode][$columnCode] ?? null)) {
            $this->optionCountByAttributeAndColumn[$attributeCode][$columnCode] = $this->countSelectOptions->forAttributeAndColumn(
                $attributeCode,
                ColumnCode::fromString($columnCode)
            );
        }

        return $this->optionCountByAttributeAndColumn[$attributeCode][$columnCode];
    }

    private function incrementCount(string $attributeCode, string $columnCode): void
    {
        $this->optionCountByAttributeAndColumn[$attributeCode][$columnCode] = $this->getCount($attributeCode, $columnCode) + 1;
    }
}
