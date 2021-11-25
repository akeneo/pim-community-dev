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

use Akeneo\Pim\TableAttribute\Domain\Value\Row;

final class TableRow
{
    public string $entityId;
    public string $attributeCode;
    public ?string $localeCode;
    public ?string $scopeCode;
    public Row $row;

    public function __construct(
        string $entityId,
        string $attributeCode,
        ?string $localeCode,
        ?string $scopeCode,
        Row $tableRow
    ) {
        $this->entityId = $entityId;
        $this->attributeCode = $attributeCode;
        $this->localeCode = $localeCode;
        $this->scopeCode = $scopeCode;
        $this->row = $tableRow;
    }
}
