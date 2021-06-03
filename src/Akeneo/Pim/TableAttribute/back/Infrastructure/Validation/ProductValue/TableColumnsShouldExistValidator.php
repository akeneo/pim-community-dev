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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class TableColumnsShouldExistValidator extends ConstraintValidator
{
    private TableConfigurationRepository $tableConfigurationRepository;

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, TableColumnsShouldExist::class);
        if (!$value instanceof TableValue) {
            return;
        }

        /** @var Table $data */
        $columnCodes = $value->getData()->uniqueColumnCodes();
        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($value->getAttributeCode());
        $existingColumnCodes = \array_map(
            fn (ColumnCode $columnCode): string => $columnCode->asString(),
            $tableConfiguration->columnCodes()
        );

        $nonExistingColumnCodes = \array_diff($columnCodes, $existingColumnCodes);
        if ([] !== $nonExistingColumnCodes) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '{{ non_existing_columns }}' => \implode(', ', $nonExistingColumnCodes),
                    '%count%' => count($nonExistingColumnCodes),
                ]
            )->addViolation();
        }
    }
}
