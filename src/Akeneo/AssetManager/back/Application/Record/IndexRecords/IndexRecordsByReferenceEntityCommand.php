<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\IndexRecords;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexRecordsByReferenceEntityCommand
{
    /** string $referenceEntity */
    public $referenceEntityIdentifier;

    public function __construct(string $referenceEntityIdentifier)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
    }
}
