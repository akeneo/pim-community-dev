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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;

final class ValidationFactory
{
    /**
     * @var array<string, array<string, string>>
     */
    private static array $mapping = [
        'number' => [
            MinValidation::KEY => MinValidation::class,
            MaxValidation::KEY => MaxValidation::class,
            DecimalsAllowedValidation::KEY => DecimalsAllowedValidation::class,
        ],
        'text' => [
            MaxLengthValidation::KEY => MaxLengthValidation::class,
        ],
    ];

    /**
     * @param mixed $value
     */
    public static function create(ColumnDataType $dataType, string $validationKey, $value): TableValidation
    {
        $class = self::$mapping[$dataType->asString()][$validationKey] ?? null;
        if (null === $class) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown "%s" validation for "%s" data type',
                $validationKey,
                $dataType->asString()
            ));
        }

        return $class::fromValue($value);
    }
}
