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

namespace Akeneo\AssetManager\Infrastructure\Controller\AssetFamily;

use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyItem;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyItemsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * List asset families
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    private FindAssetFamilyItemsInterface $findAssetFamiliesQuery;

    public function __construct(FindAssetFamilyItemsInterface $findAssetFamiliesQuery)
    {
        $this->findAssetFamiliesQuery = $findAssetFamiliesQuery;
    }

    /**
     * Get all asset families
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $assetFamilyItems = $this->findAssetFamiliesQuery->find();
        $normalizedAssetFamilyItems = $this->normalizeAssetFamilyItems($assetFamilyItems);

        return new JsonResponse([
            'items' => $normalizedAssetFamilyItems,
            'total' => count($normalizedAssetFamilyItems),
        ]);
    }

    /**
     * @param AssetFamilyItem[] $assetFamilyItems
     *
     * @return array
     */
    private function normalizeAssetFamilyItems(array $assetFamilyItems): array
    {
        return array_map(fn (AssetFamilyItem $item) => $item->normalize(), $assetFamilyItems);
    }
}
