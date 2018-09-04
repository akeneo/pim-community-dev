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

namespace Akeneo\EnrichedEntity\Application\EnrichedEntity\CreateEnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateEnrichedEntityHandler
{
    /** @var EnrichedEntityRepositoryInterface */
    private $enrichedEntityRepository;

    public function __construct(EnrichedEntityRepositoryInterface $enrichedEntityRepository)
    {
        $this->enrichedEntityRepository = $enrichedEntityRepository;
    }

    public function __invoke(CreateEnrichedEntityCommand $createEnrichedEntityCommand): void
    {
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString($createEnrichedEntityCommand->identifier),
            $createEnrichedEntityCommand->labels,
            null
        );

        $this->enrichedEntityRepository->create($enrichedEntity);
    }
}
