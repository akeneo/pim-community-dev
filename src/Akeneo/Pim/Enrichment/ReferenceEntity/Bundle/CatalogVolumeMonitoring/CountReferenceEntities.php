<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\CatalogVolumeMonitoring;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlCountReferenceEntities;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountReferenceEntities implements CountQuery
{
    private const VOLUME_NAME = 'count_reference_entity';

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
        $result = new CountVolume($volume->getVolume(), $this->limit, self::VOLUME_NAME);

        return $result;
    }
}
