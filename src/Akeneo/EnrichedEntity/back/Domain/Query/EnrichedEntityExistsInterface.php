<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Query;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface EnrichedEntityExistsInterface
{
    public function withIdentifier(EnrichedEntityIdentifier $recordIdentifier): bool;
}
