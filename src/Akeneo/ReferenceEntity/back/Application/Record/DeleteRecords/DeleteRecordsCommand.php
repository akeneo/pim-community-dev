<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\Record\DeleteRecords;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteRecordsCommand
{
    public string $referenceEntityIdentifier;
    public array $recordCodes;

    public function __construct(string $referenceEntityIdentifier, array $recordCodes)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->recordCodes = $recordCodes;
    }
}
