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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;

class LruCachedTableConfigurationRepository implements TableConfigurationRepository, CachedQueryInterface
{
    private const CACHE_SIZE = 1000;

    private TableConfigurationRepository $tableConfigurationRepository;
    private LRUCache $cache;

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        $this->cache = new LRUCache(self::CACHE_SIZE);
    }

    public function save(string $attributeCode, TableConfiguration $tableConfiguration): void
    {
        $this->tableConfigurationRepository->save($attributeCode, $tableConfiguration);
        $this->clearCache();
    }

    public function getNextIdentifier(ColumnCode $columnCode): ColumnId
    {
        return $this->tableConfigurationRepository->getNextIdentifier($columnCode);
    }

    public function getByAttributeCode(string $attributeCode): TableConfiguration
    {
        return $this->cache->getForKey(
            $attributeCode,
            fn (string $attributeCode): TableConfiguration => $this->tableConfigurationRepository->getByAttributeCode(
                $attributeCode
            )
        );
    }

    public function clearCache(): void
    {
        $this->cache = new LRUCache(self::CACHE_SIZE);
    }
}
