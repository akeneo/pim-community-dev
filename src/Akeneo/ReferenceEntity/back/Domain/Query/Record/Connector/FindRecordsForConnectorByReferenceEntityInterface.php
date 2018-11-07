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
 * Find the list of the records for a given reference entity identifier.
 * It allows to return a list for records with a code superior to the given one in the search after parameter.
 *
 * This search after method is recommended to handle big volume of data.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface FindRecordsForConnectorByReferenceEntityInterface
{
    /**
     * @return RecordForConnector[]
     */
    public function __invoke(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ?RecordCode $searchAfterCode,
        int $limit
    ): array;
}
