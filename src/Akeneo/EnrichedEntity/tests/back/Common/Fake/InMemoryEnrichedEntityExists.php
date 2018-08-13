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

namespace Akeneo\EnrichedEntity\tests\back\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntityExistsInterface;

/**
 * Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryEnrichedEntityExists implements EnrichedEntityExistsInterface
{
    /** @var InMemoryEnrichedEntityRepository */
    private $enrichedEntityRepository;

    public function __construct(InMemoryEnrichedEntityRepository $enrichedEntityRepository)
    {
        $this->enrichedEntityRepository = $enrichedEntityRepository;
    }

    public function withIdentifier(EnrichedEntityIdentifier $recordIdentifier): bool
    {
        return $this->enrichedEntityRepository->hasRecord($recordIdentifier);
    }
}
