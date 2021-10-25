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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\DTO;

final class TableRow
{
    public string $entityId;
    public string $attributeCode;
    /** @var array<string, string> */
    public array $tableRow;

    public function __construct(string $entityId, string $attributeCode, array $tableRow)
    {
        $this->entityId = $entityId;
        $this->attributeCode = $attributeCode;
        $this->tableRow = $tableRow;
    }

    public function toArray(): array
    {
        return array_merge($this->tableRow, [
            'product' => $this->entityId,
            'attribute' => $this->attributeCode,
        ]);
    }
}
