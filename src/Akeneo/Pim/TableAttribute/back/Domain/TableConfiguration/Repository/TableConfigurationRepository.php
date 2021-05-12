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

interface TableConfigurationRepository
{
    // TODO : decide if we should pass attribute id or not
    public function save(int $attributeId, TableConfiguration $tableConfiguration): void;

    public function getByAttributeId(int $attributeId): TableConfiguration;
}
