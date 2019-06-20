<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\CatalogVolumeMonitoring;

use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlCountAssetFamilies;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountAssetFamilies implements CountQuery
{
    private const VOLUME_NAME = 'count_asset_family';

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
        $result = new CountVolume($volume->getVolume(), $this->limit, self::VOLUME_NAME);

        return $result;
    }
}
