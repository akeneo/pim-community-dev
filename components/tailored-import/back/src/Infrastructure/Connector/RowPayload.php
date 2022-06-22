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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Platform\TailoredImport\Domain\Model\ColumnCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;

class RowPayload
{
    private ?UpsertProductCommand $upsertProductCommand = null;
    /** @var InvalidValue[] */
    private ?array $invalidValues = null;

    public function __construct(
        private Row $row,
        private ColumnCollection $columnCollection,
        private int $rowPosition,
    ) {
    }

    public function setUpsertProductCommand(?UpsertProductCommand $upsertProductCommand): void
    {
        $this->upsertProductCommand = $upsertProductCommand;
    }

    public function getUpsertProductCommand(): ?UpsertProductCommand
    {
        return $this->upsertProductCommand;
    }

    /**
     * @param InvalidValue[]
     */
    public function setInvalidValues(array $invalidValues): void
    {
        $this->invalidValues = $invalidValues;
    }

    /**
     * @return null|InvalidValue[]
     */
    public function getInvalidValues(): ?array
    {
        return $this->invalidValues;
    }

    public function getRow(): Row
    {
        return $this->row;
    }

    public function getRowPosition(): int
    {
        return $this->rowPosition;
    }

    public function getColumnCollection(): ColumnCollection
    {
        return $this->columnCollection;
    }
}
