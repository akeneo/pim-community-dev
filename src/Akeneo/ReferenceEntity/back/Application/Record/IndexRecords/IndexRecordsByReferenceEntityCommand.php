<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\IndexRecords;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexRecordsByReferenceEntityCommand
{
    public function __construct(
        public string $referenceEntityIdentifier
    ) {
    }
}
