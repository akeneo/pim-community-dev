<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityList;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindEnrichedEntitiesQuery
{
    /** @var EnrichedEntityRepository */
    private $enrichedEntityRepository;

    /**
     * @param EnrichedEntityRepository $enrichedEntityRepository
     */
    public function __construct(EnrichedEntityRepository $enrichedEntityRepository)
    {
        $this->enrichedEntityRepository = $enrichedEntityRepository;
    }

    /**
     * @return EnrichedEntityItem[]
     */
    public function __invoke(): array
    {
        $all = $this->enrichedEntityRepository->all();
        $items = array_map(function (EnrichedEntity $enrichedEntity) {
            return EnrichedEntityItem::fromEntity($enrichedEntity);
        }, $all);

        return $items;
    }
}
