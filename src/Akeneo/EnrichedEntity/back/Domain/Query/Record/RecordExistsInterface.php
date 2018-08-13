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

namespace Akeneo\EnrichedEntity\Domain\Query\Record;

use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;

interface RecordExistsInterface
{
    public function withIdentifier(RecordIdentifier $recordIdentifier): bool;
}
