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
                'The "{{ attribute }}" attribute does not exist',
                [
                    '{{ attribute }}' => $value->attributeCode(),
                ]
            )->atPath('attribute')->addViolation();

            return;
        }

        if (AttributeTypes::TABLE !== $attribute->type()) {
            $this->context->buildViolation(
                'The "{{ attribute }}" attribute is not a table attribute',
                [
                    '{{ attribute }}' => $value->attributeCode(),
                ]
            )->atPath('attribute')->addViolation();

            return;
        }

        $configuration = $this->tableConfigurationRepository->getByAttributeCode($value->attributeCode());
        $columnCodes = \array_map(
            fn (ColumnCode $columnCode): string => $columnCode->asString(),
            $configuration->columnCodes()
        );
        if (!\in_array($value->columnCode(), $columnCodes)) {
            $this->context->buildViolation(
                'The "{{ column }}" column does not exist for the "{{ attribute }}" attribute',
                [
                    '{{ attribute }}' => $value->attributeCode(),
                    '{{ column }}' => $value->columnCode(),
                ]
            )->atPath('column')->addViolation();

            return;
        }

        $dataType = $configuration->getColumnDataType(ColumnCode::fromString($value->columnCode()))->asString();
        if (SelectColumn::DATATYPE !== $dataType) {
            $this->context->buildViolation(
                'The "{{ column }}" column of the "{{ attribute }}" attribute is not a "select".',
                [
                    '{{ attribute }}' => $value->attributeCode(),
                    '{{ column }}' => $value->columnCode(),
                ]
            )->atPath('column')->addViolation();
        }
    }
}
