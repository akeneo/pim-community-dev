<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface FindLinkedRecordsInterface
{
    /** @return RecordIdentifier[] */
    public function __invoke(RecordIdentifier $recordIdentifier): array;
}
