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

namespace Akeneo\EnrichedEntity\back\Application\EnrichedEntity\Edit;

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

    public function __invoke(string $rawIdentifier, array $data)
    {
        $identifier = EnrichedEntityIdentifier::fromString($rawIdentifier);
        $enrichedEntityToEdit = $this->enrichedEntityRepository->findOneByIdentifier($identifier);

        $labels = LabelCollection::fromArray($data['labels']);
        $editedEnrichedEntity = $enrichedEntityToEdit->updateLabels($labels);

//        $this->enrichedEntityRepository->
    }
}
