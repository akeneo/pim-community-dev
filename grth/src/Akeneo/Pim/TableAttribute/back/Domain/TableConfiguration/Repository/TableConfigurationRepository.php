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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;

interface TableConfigurationRepository
{
    public function getNextIdentifier(ColumnCode $columnCode): ColumnId;

    public function save(string $attributeCode, TableConfiguration $tableConfiguration): void;

    /**
     * @throws TableConfigurationNotFoundException
     */
    public function getByAttributeCode(string $attributeCode): TableConfiguration;
}
