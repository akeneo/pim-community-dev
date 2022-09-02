<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Cache\AssetFamily;

use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyItem;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyItemsInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LRUCachedFindAssetFamilyItems implements FindAssetFamilyItemsInterface
{
    /** @var array<AssetFamilyItem>|null */
    private ?array $cachedAssetFamilyItems = null;

    public function __construct(private FindAssetFamilyItemsInterface $findAssetFamilyItems)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function find(): array
    {
        if (null === $this->cachedAssetFamilyItems) {
            $this->cachedAssetFamilyItems = $this->findAssetFamilyItems->find();
        }

        return $this->cachedAssetFamilyItems;
    }
}
