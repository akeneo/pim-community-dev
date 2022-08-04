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

use Webmozart\Assert\Assert;

final class FormatFloatOperation implements OperationInterface
{
    public const TYPE = 'format_float';

    public function __construct(
        private string $uuid,
        private string $decimalSeparator,
    ) {
        Assert::uuid($uuid);
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'uuid' => $this->uuid,
            'decimal_separator' => $this->decimalSeparator,
        ];
    }
}
