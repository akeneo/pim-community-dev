<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\EnrichedEntity\Context;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\ListEnrichedEntityHandler;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use Behat\Behat\Context\Context;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class EditEnrichedEntityContext implements Context
{
    /** @var ListEnrichedEntityHandler */
    private $listEnrichedEntityHandler;

    /** @var EnrichedEntityRepository */
    private $enrichedEntityRepository;

    /** @var EnrichedEntity[] */
    private $entitiesFound;

    /**
     * @param ListEnrichedEntityHandler $listEnrichedEntityHandler
     * @param EnrichedEntityRepository  $enrichedEntityRepository
     */
    public function __construct(
        ListEnrichedEntityHandler $listEnrichedEntityHandler,
        EnrichedEntityRepository $enrichedEntityRepository
    ) {
        $this->listEnrichedEntityHandler = $listEnrichedEntityHandler;
        $this->enrichedEntityRepository = $enrichedEntityRepository;
    }
}
