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

namespace Akeneo\EnrichedEntity\Application\EnrichedEntity\EditEnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepository;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditEnrichedEntityHandler
{
    /** @var EnrichedEntityRepository */
    private $enrichedEntityRepository;

    public function __construct(EnrichedEntityRepository $enrichedEntityRepository)
    {
        $this->enrichedEntityRepository = $enrichedEntityRepository;
    }

    public function __invoke(EditEnrichedEntityCommand $editEnrichedEntityCommand): void
    {
        $identifier = EnrichedEntityIdentifier::fromString($editEnrichedEntityCommand->identifier);
        $labelCollection = LabelCollection::fromArray($editEnrichedEntityCommand->labels);

        $enrichedEntity = $this->enrichedEntityRepository->getByIdentifier($identifier);
        $enrichedEntity->updateLabels($labelCollection);
        $this->enrichedEntityRepository->update($enrichedEntity);
    }
}
