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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\Value\Cell;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class CellDataTypesShouldMatchValidator extends ConstraintValidator
{
    private TableConfigurationRepository $tableConfigurationRepository;

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    public function validate($tableValue, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, CellDataTypesShouldMatch::class);
        if (!$tableValue instanceof TableValue) {
            return;
        }

        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($tableValue->getAttributeCode());
        $table = $tableValue->getData();

        foreach ($table as $rowIndex => $row) {
            /** @var Cell $cell */
            foreach ($row as $stringColumnId => $cell) {
                try {
                    $columnId = ColumnId::fromString((string) $stringColumnId);
                } catch (\Exception $e) {
                    continue;
                }
                $column = $tableConfiguration->getColumn($columnId);
                if (null === $column) {
                    continue;
                }

                $stringColumnCode = $column->code()->asString();
                $data = $cell->normalize();
                switch ($column->dataType()->asString()) {
                    case 'text':
                    case 'reference_entity':
                    case 'select':
                        if (!is_string($data)) {
                            $this->addViolation('string', $data, $rowIndex, $stringColumnCode);
                        }
                        break;
                    case 'number':
                        if (!is_numeric($data)) {
                            $this->addViolation('numeric', $data, $rowIndex, $stringColumnCode);
                        }
                        break;
                    case 'boolean':
                        if (!is_bool($data)) {
                            $this->addViolation('boolean', $data, $rowIndex, $stringColumnCode);
                        }
                        break;
                    case 'measurement':
                        $unitCode = $data['unit'] ?? null;
                        $amount = $data['amount'] ?? null;
                        if (!\is_array($data) || !\is_string($unitCode) || !\is_numeric($amount)) {
                            $this->context
                                ->buildViolation(
                                    'pim_table_configuration.validation.product_value.unexpected_data_type_for_measurement',
                                    [
                                        '{{ given }}' => \json_encode($data),
                                        '{{ columnCode }}' => $stringColumnCode,
                                    ]
                                )
                                ->atPath(sprintf('[%d].%s', $rowIndex, $stringColumnCode))
                                ->addViolation();
                        }
                        break;
                    default:
                        throw new \LogicException(
                            sprintf('Cannot validate the "%s" data type', $column->dataType()->asString())
                        );
                }
            }
        }
    }

    private function addViolation(string $expected, $data, int $rowIndex, string $columnCode): void
    {
        $given = is_object($data) ? get_class($data) : gettype($data);

        $this->context
            ->buildViolation(
                'pim_table_configuration.validation.product_value.unexpected_data_type',
                [
                    '{{ expected }}' => $expected,
                    '{{ given }}' => $given,
                    '{{ columnCode }}' => $columnCode,
                ]
            )
            ->atPath(sprintf('[%d].%s', $rowIndex, $columnCode))
            ->addViolation();
    }
}
