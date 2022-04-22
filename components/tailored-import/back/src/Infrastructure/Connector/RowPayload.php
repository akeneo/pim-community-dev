<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Platform\TailoredImport\Domain\Model\ColumnCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RowPayload
{
    private ?UpsertProductCommand $upsertProductCommand = null;

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
