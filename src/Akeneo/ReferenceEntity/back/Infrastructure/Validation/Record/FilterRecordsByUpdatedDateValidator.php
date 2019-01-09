<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FilterRecordsByUpdatedDateValidator extends ConstraintValidator
{
    const SUPPORTED_OPERATOR = '>';

    /**
     * {@inheritdoc}
     */
    public function validate($filters, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $updatedFilter = $this->getUpdatedFilter($filters);

        if (null === $updatedFilter) {
            return;
        }

        $this->validateDate($updatedFilter['value']);
        $this->validateOperator($updatedFilter['operator']);
    }

    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof FilterRecordsByUpdatedDate) {
            throw new UnexpectedTypeException($constraint, FilterRecordsByUpdatedDate::class);
        }
    }

    private function getUpdatedFilter(array $filters): ?array
    {
        $updatedFilter = current(array_filter($filters, function ($filter) {
            return $filter['field'] === (string) 'updated';
        }));

        if (false === $updatedFilter) {
            return null;
        }

        return $updatedFilter;
    }

    private function validateDate(?string $date)
    {
        try {
            new \DateTime($date);
        } catch (\Exception $e) {
            $this->context->buildViolation(sprintf(FilterRecordsByUpdatedDate::DATE_SHOULD_BE_VALID, $date))
                ->atPath('updated.value')
                ->addViolation();
        }
    }

    private function validateOperator(?string $operator)
    {
        $operatorIsValid = $operator === self::SUPPORTED_OPERATOR;

        if (false === $operatorIsValid) {
            $this->context->buildViolation(sprintf(FilterRecordsByUpdatedDate::OPERATOR_SHOULD_BE_SUPPORTED, $operator))
                ->atPath('updated.operator')
                ->addViolation();
        }
    }
}
