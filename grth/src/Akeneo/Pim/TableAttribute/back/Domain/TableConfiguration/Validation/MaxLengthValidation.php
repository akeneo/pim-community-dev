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

use Webmozart\Assert\Assert;

final class MaxLengthValidation implements TableValidation
{
    public const KEY = 'max_length';

    private int $maxLength;

    private function __construct(int $maxLength)
    {
        $this->maxLength = $maxLength;
    }

    public static function fromValue($maxLength): TableValidation
    {
        Assert::integer($maxLength);
        Assert::greaterThan($maxLength, 0);

        return new self($maxLength);
    }

    public function getValue(): int
    {
        return $this->maxLength;
    }
}
