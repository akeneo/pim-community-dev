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

use Akeneo\Platform\TailoredImport\Domain\Exception\MismatchedFileHeadersException;
use Akeneo\Platform\TailoredImport\Domain\Exception\MissingJobConfigurationException;
use Akeneo\Platform\TailoredImport\Domain\Model\ColumnCollection;
use Webmozart\Assert\Assert;

/**
 * @implements \IteratorAggregate<int, FileHeader>
 */
class FileHeaderCollection implements \Countable, \IteratorAggregate
{
    private function __construct(
        private array $fileHeaders,
    ) {
        Assert::allIsInstanceOf($fileHeaders, FileHeader::class);
    }

    public static function createFromNormalized(array $normalizedFileHeaders): self
    {
        $fileHeaderInstances = array_map(
            static fn (array $normalizedFileHeader) => FileHeader::createFromNormalized($normalizedFileHeader),
            $normalizedFileHeaders,
        );

        return new self($fileHeaderInstances);
    }

    public function assertColumnMatch(ColumnCollection $columnCollection): bool
    {
        $fileHeaderIterator = $this->getIterator();
        $columnIterator = $columnCollection->getIterator();

        if (0 === $columnIterator->count()) {
            throw new MissingJobConfigurationException();
        }

        if ($fileHeaderIterator->count() !== $columnIterator->count()) {
            throw new MismatchedFileHeadersException($columnCollection->getLabels(), array_map(fn (FileHeader $fileHeader) => $fileHeader->getLabel(), $this->fileHeaders));
        }

        while ($fileHeaderIterator->valid()) {
            $currentFileHeader = $fileHeaderIterator->current();
            $currentColumn = $columnIterator->current();

            if (!$currentFileHeader->matchToColumn($currentColumn)) {
                throw new MismatchedFileHeadersException($columnCollection->getLabels(), array_map(fn (FileHeader $fileHeader) => $fileHeader->getLabel(), $this->fileHeaders));
            }

            $fileHeaderIterator->next();
            $columnIterator->next();
        }

        return true;
    }

    public function count(): int
    {
        return count($this->fileHeaders);
    }

    /**
     * @return \ArrayIterator<int, FileHeader>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->fileHeaders);
    }
}
