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
    public const MAXIMUM_COLUMN_COUNT = 500;
    public const MINIMUM_HEADER_LINE = 1;
    public const MAXIMUM_HEADER_LINE = 19;
    public const MAXIMUM_FIRST_PRODUCT_LINE = 20;

    private function __construct(
        private int $firstColumn,
        private int $headerLine,
        private int $productLine,
        private ?string $sheetName,
    ) {
        Assert::greaterThanEq($firstColumn, 0);
        Assert::lessThanEq($firstColumn, self::MAXIMUM_COLUMN_COUNT);
        Assert::greaterThanEq($headerLine, self::MINIMUM_HEADER_LINE);
        Assert::lessThanEq($headerLine, self::MAXIMUM_HEADER_LINE);
        Assert::greaterThanEq($productLine, $headerLine);
        Assert::lessThanEq($productLine, self::MAXIMUM_FIRST_PRODUCT_LINE);
        Assert::nullOrNotEmpty($sheetName);
    }

    public static function create(
        int $firstColumn,
        int $headerLine,
        int $productLine,
        ?string $sheetName,
    ): self {
        return new self(
            $firstColumn,
            $headerLine,
            $productLine,
            $sheetName,
        );
    }

    public static function createFromNormalized(array $normalizedFileStructure): self
    {
        return new self(
            (int) $normalizedFileStructure['first_column'],
            (int) $normalizedFileStructure['header_row'],
            (int) $normalizedFileStructure['first_product_row'],
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
