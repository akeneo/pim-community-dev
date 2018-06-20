<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
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

        $enrichedEntity = $this->enrichedEntityRepository->getByIdentifier($identifier);

        return EnrichedEntityDetails::fromEntity($enrichedEntity);
    }
}
