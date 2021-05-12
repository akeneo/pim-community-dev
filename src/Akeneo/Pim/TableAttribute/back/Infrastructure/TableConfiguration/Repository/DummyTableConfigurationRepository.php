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

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Repository;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;

class DummyTableConfigurationRepository implements TableConfigurationRepository
{
    public function save(int $attributeId, TableConfiguration $tableConfiguration): void
    {
        \file_put_contents(
            '/srv/pim/var/' . $attributeId . '.json',
            \json_encode($tableConfiguration->normalize())
        );
    }

    public function getByAttributeId(int $attributeId): TableConfiguration
    {
        if (!is_file('/srv/pim/var/' . $attributeId . '.json')) {
            throw new \Exception('not found');
        }
        $normalized = \file_get_contents('/srv/pim/var/' . $attributeId . '.json');

        return TableConfiguration::fromColumnDefinitions(
            array_map(
                fn (array $rawColumnDefinition): ColumnDefinition => TextColumn::fromNormalized($rawColumnDefinition),
                \json_decode($normalized, true),
            )
        );
    }
}
