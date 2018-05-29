<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Application\EnrichedEntity\Show;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;

/**
 * This class could have not have been written and we could have directly used the repository in the application layer.
 *
 * However, we decided to put it in for the sake of the use case discovery.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ShowEnrichedEntityHandler
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
     * @param EnrichedEntityIdentifier $identifier
     *
     * @return EnrichedEntity
     */
    public function findByIdentifier(EnrichedEntityIdentifier $identifier): EnrichedEntity
    {
        return $this->enrichedEntityRepository->findOneByIdentifier($identifier);
    }

    /**
     * @return EnrichedEntity[]
     */
    public function findAll(): array
    {
        return $this->enrichedEntityRepository->all();
    }
}
