<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyItem;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyItemsInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAssetFamilyItems implements FindAssetFamilyItemsInterface
{
    /** @var AssetFamilyItem[] */
    private array $results = [];

    public function save(AssetFamilyItem $assetFamilyDetails)
    {
        $this->results[] = $assetFamilyDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function find(): array
    {
        return $this->results;
    }
}
