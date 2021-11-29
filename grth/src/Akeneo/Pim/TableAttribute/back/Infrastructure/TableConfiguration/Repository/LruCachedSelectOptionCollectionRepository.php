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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;

final class LruCachedSelectOptionCollectionRepository implements SelectOptionCollectionRepository, CachedQueryInterface
{
    private SelectOptionCollectionRepository $selectOptionCollectionRepository;
    private LRUCache $cache;

    public function __construct(SelectOptionCollectionRepository $selectOptionCollectionRepository)
    {
        $this->selectOptionCollectionRepository = $selectOptionCollectionRepository;
        $this->cache = new LRUCache(3);
    }

    public function save(
        string $attributeCode,
        ColumnCode $columnCode,
        WriteSelectOptionCollection $selectOptionCollection
    ): void {
        $this->selectOptionCollectionRepository->save($attributeCode, $columnCode, $selectOptionCollection);
        $this->clearCache();
    }

    public function getByColumn(string $attributeCode, ColumnCode $columnCode): SelectOptionCollection
    {
        $notFoundOptionCollectionCallback = function (string $key): SelectOptionCollection {
            [$attributeCode, $columnCode] = \explode('-', $key);

            return $this->selectOptionCollectionRepository->getByColumn(
                $attributeCode,
                ColumnCode::fromString($columnCode)
            );
        };

        return $this->cache->getForKey(
            \sprintf('%s-%s', $attributeCode, $columnCode->asString()),
            $notFoundOptionCollectionCallback
        );
    }

    public function upsert(
        string $attributeCode,
        ColumnCode $columnCode,
        SelectOptionCollection $selectOptionCollection
    ): void {
        $this->selectOptionCollectionRepository->upsert($attributeCode, $columnCode, $selectOptionCollection);
        $this->clearCache();
    }

    public function clearCache(): void
    {
        $this->cache = new LRUCache(3);
    }
}
