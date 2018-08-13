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

namespace Akeneo\EnrichedEntity\Application\EnrichedEntity\DeleteEnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteEnrichedEntityHandler
{
    /** @var EnrichedEntityRepositoryInterface */
    private $enrichedEntityRepository;

    public function __construct(EnrichedEntityRepositoryInterface $enrichedEntityRepository)
    {
        $this->enrichedEntityRepository = $enrichedEntityRepository;
    }

    public function __invoke(DeleteEnrichedEntityCommand $deleteEnrichedEntityCommand): void
    {
        $identifier = EnrichedEntityIdentifier::fromString($deleteEnrichedEntityCommand->identifier);

        $this->enrichedEntityRepository->deleteByIdentifier($identifier);
    }
}
