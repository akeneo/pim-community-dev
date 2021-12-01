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

namespace Akeneo\ReferenceEntity\Application\Record\MassDeleteRecords;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassDeleteRecordsCommand
{
    public string $referenceEntityIdentifier;
    public array $query;

    public function __construct(string $referenceEntityIdentifier, array $query)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->query = $query;
    }
}
