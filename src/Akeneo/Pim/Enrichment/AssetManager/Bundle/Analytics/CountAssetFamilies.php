<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\Analytics;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\CountVolume as AssetFamilyCountVolume;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlCountAssetFamilies;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CountAssetFamilies implements CountQuery
{
    /** @var SqlCountAssetFamilies */
    private $sqlCountAssetFamilies;
    /** @var int */
    private $limit;

    public function __construct(SqlCountAssetFamilies $sqlCountAssetFamilies, int $limit)
    {
        $this->sqlCountAssetFamilies = $sqlCountAssetFamilies;
        $this->limit = $limit;
    }

    public function fetch(): CountVolume
    {
        $volume = $this->sqlCountAssetFamilies->fetch();

        return $this->adapt($volume);
    }

    private function adapt(AssetFamilyCountVolume $volume): CountVolume
    {
        return new CountVolume($volume->getVolume(), $this->limit, $volume->getVolumeName());
    }
}
