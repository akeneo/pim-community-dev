<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject;

final class IsRequiredForCompleteness
{
    private bool $isRequiredForCompleteness;

    private function __construct(bool $isRequiredForCompleteness)
    {
        $this->isRequiredForCompleteness = $isRequiredForCompleteness;
    }

    public static function fromBoolean(bool $isRequiredForCompleteness): self
    {
        return new self($isRequiredForCompleteness);
    }

    public function asBoolean(): bool
    {
        return $this->isRequiredForCompleteness;
    }
}
