<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\EnrichedEntity\Context;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityList\FindEnrichedEntitiesQuery;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use Behat\Behat\Context\Context;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class EditEnrichedEntityContext implements Context
{
    /** @var FindEnrichedEntitiesQuery */
    private $findEnrichedEntitiesQuery;

    /** @var EnrichedEntityRepository */
    private $enrichedEntityRepository;

    /** @var EnrichedEntity[] */
    private $entitiesFound;

    /**
     * @param FindEnrichedEntitiesQuery $findEnrichedEntitiesQuery
     * @param EnrichedEntityRepository  $enrichedEntityRepository
     */
    public function __construct(
        FindEnrichedEntitiesQuery $findEnrichedEntitiesQuery,
        EnrichedEntityRepository $enrichedEntityRepository
    ) {
        $this->findEnrichedEntitiesQuery = $findEnrichedEntitiesQuery;
        $this->enrichedEntityRepository = $enrichedEntityRepository;
    }
}
