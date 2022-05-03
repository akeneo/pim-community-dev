<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

final class DecimalFormatterOperation implements OperationInterface
{
    public const TYPE = 'decimal_formatter';

    public function __construct(
        private string $decimalSeparator,
    ) {
    }

    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'decimal_separator' => $this->decimalSeparator,
        ];
    }
}
