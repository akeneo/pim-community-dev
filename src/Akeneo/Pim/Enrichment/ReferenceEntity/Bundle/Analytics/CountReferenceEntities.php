<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Analytics;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\CountVolume as ReferenceEntityCountVolume;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlCountReferenceEntities;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CountReferenceEntities implements CountQuery
{
    /** @var SqlCountReferenceEntities */
    private $sqlCountReferenceEntities;
    /** @var int */
    private $limit;

    public function __construct(SqlCountReferenceEntities $sqlCountReferenceEntities, int $limit)
    {
        $this->sqlCountReferenceEntities = $sqlCountReferenceEntities;
        $this->limit = $limit;
    }

    public function fetch(): CountVolume
    {
        $volume = $this->sqlCountReferenceEntities->fetch();

        return $this->adapt($volume);
    }

    private function adapt(ReferenceEntityCountVolume $volume): CountVolume
    {
        return new CountVolume($volume->getVolume(), $this->limit, $volume->getVolumeName());
    }
}
