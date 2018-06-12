<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindEnrichedEntityQuery
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
     * @param string $rawIdentifier
     *
     * @return EnrichedEntityDetails
     */
    public function __invoke(string $rawIdentifier): EnrichedEntityDetails
    {
        $identifier = EnrichedEntityIdentifier::fromString($rawIdentifier);

        $enrichedEntity = $this->enrichedEntityRepository->findOneByIdentifier($identifier);

        return EnrichedEntityDetails::fromEntity($enrichedEntity);
    }
}
