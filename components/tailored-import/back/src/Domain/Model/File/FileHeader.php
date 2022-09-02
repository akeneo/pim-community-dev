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

namespace Akeneo\Platform\TailoredImport\Domain\Model\File;

use Akeneo\Platform\TailoredImport\Domain\Model\Column;
use Webmozart\Assert\Assert;

class FileHeader
{
    private function __construct(
        private int $index,
        private string $label,
    ) {
        Assert::greaterThanEq($index, 0);
        Assert::stringNotEmpty($label);
    }

    public static function createFromNormalized(array $normalizedFileHeader): self
    {
        return new self(
            $normalizedFileHeader['index'],
            $normalizedFileHeader['label'],
        );
    }

    public function matchToColumn(Column $column): bool
    {
        return $this->index === $column->getIndex() && $this->label === $column->getLabel();
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
