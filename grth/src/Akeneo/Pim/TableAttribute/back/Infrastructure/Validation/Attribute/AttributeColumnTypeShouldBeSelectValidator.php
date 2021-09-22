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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class AttributeColumnTypeShouldBeSelectValidator extends ConstraintValidator
{
    private GetAttributes $getAttributes;
    private TableConfigurationRepository $tableConfigurationRepository;

    public function __construct(GetAttributes $getAttributes, TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->getAttributes = $getAttributes;
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, AttributeColumnTypeShouldBeSelect::class);
        Assert::isInstanceOf($value, SelectOptionDetails::class);

        /** @var SelectOptionDetails $value */
        $attribute = $this->getAttributes->forCode($value->attributeCode());
        if (null === $attribute) {
            $this->context->buildViolation(
                'pim_table_configuration.validation.table_configuration.attribute_does_not_exist',
                [
                    '{{ attribute }}' => $value->attributeCode(),
                ]
            )->atPath('attribute')->addViolation();

            return;
        }

        if (AttributeTypes::TABLE !== $attribute->type()) {
            $this->context->buildViolation(
                'pim_table_configuration.validation.table_configuration.not_table_attribute',
                [
                    '{{ attribute }}' => $value->attributeCode(),
                ]
            )->atPath('attribute')->addViolation();

            return;
        }

        $configuration = $this->tableConfigurationRepository->getByAttributeCode($value->attributeCode());
        $column = $configuration->getColumnByCode(ColumnCode::fromString($value->columnCode()));
        if (null === $column) {
            $this->context->buildViolation(
                'pim_table_configuration.validation.table_configuration.column_does_not_exist',
                [
                    '{{ attribute }}' => $value->attributeCode(),
                    '{{ column }}' => $value->columnCode(),
                ]
            )->atPath('column')->addViolation();

            return;
        }

        if (SelectColumn::DATATYPE !== $column->dataType()->asString()) {
            $this->context->buildViolation(
                'pim_table_configuration.validation.table_configuration.not_select_column',
                [
                    '{{ attribute }}' => $value->attributeCode(),
                    '{{ column }}' => $value->columnCode(),
                ]
            )->atPath('column')->addViolation();
        }
    }
}
