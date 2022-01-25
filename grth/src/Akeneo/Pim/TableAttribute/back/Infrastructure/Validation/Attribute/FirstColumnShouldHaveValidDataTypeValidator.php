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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class FirstColumnShouldHaveValidDataTypeValidator extends ConstraintValidator
{
    private array $allowedFirstColumnDataTypes;

    public function __construct(array $allowedFirstColumnDataTypes)
    {
        Assert::allString($allowedFirstColumnDataTypes);
        $this->allowedFirstColumnDataTypes = $allowedFirstColumnDataTypes;
    }

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, FirstColumnShouldHaveValidDataType::class);
        if (!\is_array($value) || [] === $value) {
            return;
        }

        $firstColumnDefinition = current($value);
        $firstColumnDataType = $firstColumnDefinition['data_type'] ?? null;

        if (\is_string($firstColumnDataType) && !\in_array($firstColumnDataType, $this->allowedFirstColumnDataTypes)) {
            $allowedDataTypesExceptLast = \array_slice($this->allowedFirstColumnDataTypes, 0, \count($this->allowedFirstColumnDataTypes) - 1);
            $this->context
                ->buildViolation(
                    $constraint->message,
                    [
                        '{{ data_type }}' => $firstColumnDataType,
                        '{{ allowed_data_types }}' => implode(', ', $this->allowedFirstColumnDataTypes),
                        '{{ allowed_data_types_except_last }}' => \implode(
                            ', ',
                            array_map(
                                fn (string $dataType) => \sprintf('"%s"', $dataType),
                                $allowedDataTypesExceptLast
                            )
                        ),
                        '{{ last_allowed_data_types }}' => \end($this->allowedFirstColumnDataTypes),
                        '%count%' => count($this->allowedFirstColumnDataTypes)
                    ]
                )
                ->atPath('[0].data_type')
                ->addViolation();
        }
    }
}
