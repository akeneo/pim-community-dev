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

namespace Akeneo\Platform\TailoredImport\Domain\Model\Value;

use Webmozart\Assert\Assert;

final class BooleanValue implements ValueInterface
{
    private const TYPE = 'boolean';

    public function __construct(
        private bool $value,
    ) {
        Assert::boolean($value);
    }

    public function getValue(): bool
    {
        return $this->value;
    }

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'value' => $this->value,
        ];
    }
}
