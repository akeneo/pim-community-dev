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

namespace Akeneo\EnrichedEntity\back\Application\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditEnrichedEntityHandler
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
     * @param array  $labels
     *
     * @return EnrichedEntity
     */
    public function __invoke(string $rawIdentifier, array $labels): EnrichedEntity
    {
        $identifier = EnrichedEntityIdentifier::fromString($rawIdentifier);
        $labelCollection = LabelCollection::fromArray($labels);

        $enrichedEntity = $this->enrichedEntityRepository->findOneByIdentifier($identifier);
        $enrichedEntity->updateLabels($labelCollection);
        $this->enrichedEntityRepository->update($enrichedEntity);

        return $enrichedEntity;
    }
}
