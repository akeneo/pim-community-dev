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

namespace Akeneo\ReferenceEntity\Domain\Query\Record\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Find the paginated list of the records of a given reference entity identifier.
 * The pagination is managed by giving the code of the record to search after and the maximum number of records to return.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface FindRecordsForConnectorByReferenceEntityInterface
{
    public function __invoke(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ?RecordCode $searchAfterCode,
        int $limit
    ): array;
}
