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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class UpdatedDateValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($filters, Constraint $constraint)
    {
        $updatedFilter = $this->getUpdatedFilter($filters);

        if (null === $updatedFilter) {
            return;
        }

        if (false === $this->dateIsValid($updatedFilter['value'])) {
            $this->context->buildViolation(sprintf('Property "updated" expects a string with the ISO 8601 format, "%s" given.', $updatedFilter['value']))
                ->atPath('filters')
                ->addViolation();
        }
    }

    private function getUpdatedFilter(array $filters): ?array {
        $updatedFilter = current(array_filter($filters, function ($filter)  {
            return $filter['field'] === (string) 'updated';
        }));

        if (false === $updatedFilter) {
            return null;
        }

        return $updatedFilter;
    }

    private function dateIsValid(?string $date): bool
    {
        try {
            new \DateTime($date);
            return true;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return false;
        }
    }
}
