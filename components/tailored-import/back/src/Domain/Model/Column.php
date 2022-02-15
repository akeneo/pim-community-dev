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

namespace Akeneo\Platform\TailoredImport\Domain\Model;

use Webmozart\Assert\Assert;

class Column
{
    private function __construct(
        private string $uuid,
        private int $index,
        private string $label,
    ) {
        Assert::uuid($uuid);
        Assert::greaterThanEq($index, 0);
        Assert::stringNotEmpty($label);
    }

    public static function createFromNormalized(array $normalizedColumn): self
    {
        return new self(
            $normalizedColumn['uuid'],
            $normalizedColumn['index'],
            $normalizedColumn['label'],
        );
    }

    public function index(): int
    {
        return $this->index;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }
}
