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

use Webmozart\Assert\Assert;

class FileStructure
{
    private function __construct(
        private int $firstColumn,
        private int $headerLine,
        private int $productLine,
        private ?string $sheetName,
    ) {
        Assert::greaterThanEq($firstColumn, 0);
        Assert::greaterThanEq($headerLine, 0);
        Assert::greaterThanEq($productLine, $headerLine);
        Assert::nullOrNotEmpty($sheetName);
    }

    public static function createFromNormalized(array $normalizedFileStructure): self
    {
        return new self(
            (int) $normalizedFileStructure['first_column'],
            (int) $normalizedFileStructure['header_line'],
            (int) $normalizedFileStructure['product_line'],
            $normalizedFileStructure['sheet_name'],
        );
    }

    public function getFirstColumn(): int
    {
        return $this->firstColumn;
    }

    public function getHeaderLine(): int
    {
        return $this->headerLine;
    }

    public function getProductLine(): int
    {
        return $this->productLine;
    }

    public function getSheetName(): ?string
    {
        return $this->sheetName;
    }
}
