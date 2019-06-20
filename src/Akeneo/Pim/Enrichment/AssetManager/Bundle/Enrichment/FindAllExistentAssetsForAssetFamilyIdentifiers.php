<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\Enrichment;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\SqlFindAllExistentAssetsForAssetFamilyIdentifiers;
use Akeneo\Pim\Enrichment\AssetManager\Component\Query\FindAllExistentAssetsForAssetFamilyIdentifiers as QueryInterface;

/**
 * This class is an adapter to the implementation of the same query in another Bounded context
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class FindAllExistentAssetsForAssetFamilyIdentifiers implements QueryInterface
{
    /** @var SqlFindAllExistentAssetsForAssetFamilyIdentifiers */
    private $allExistentAssetForAssetFamilyIdentifiers;

    public function __construct(SqlFindAllExistentAssetsForAssetFamilyIdentifiers $allExistentAssetForAssetFamilyIdentifiers)
    {
        $this->allExistentAssetForAssetFamilyIdentifiers = $allExistentAssetForAssetFamilyIdentifiers;
    }

    public function forAssetFamilyIdentifiersAndAssetCodes(array $assetFamilyIdentifiersToCodes): array
    {
        return $this
            ->allExistentAssetForAssetFamilyIdentifiers
            ->forAssetFamilyIdentifiersAndAssetCodes($assetFamilyIdentifiersToCodes);
    }
}
