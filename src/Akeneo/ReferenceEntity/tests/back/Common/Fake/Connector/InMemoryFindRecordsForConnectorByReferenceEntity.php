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

namespace Akeneo\ReferenceEntity\Common\Fake\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindRecordsForConnectorByReferenceEntityInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\RecordForConnector;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordsForConnectorByReferenceEntity implements FindRecordsForConnectorByReferenceEntityInterface
{
    /** @var RecordForConnector[] */
    private $recordsByEntity;

    public function __construct()
    {
        $this->recordsByEntity = [];
    }

    public function save(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $recordCode,
        RecordForConnector $recordForConnector
    ): void {
        $this->recordsByEntity[(string) $referenceEntityIdentifier][(string) $recordCode] = $recordForConnector;
    }

    public function __invoke(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ?RecordCode $searchAfterCode,
        int $limit
    ): array {
        $entityRecords = $this->recordsByEntity[(string) $referenceEntityIdentifier] ?? [];

        if (null !== $searchAfterCode) {
            $searchAfterCode = (string) $searchAfterCode;
            $entityRecords = array_filter($entityRecords, function ($recordCode) use ($searchAfterCode) {
                return strcasecmp($recordCode, $searchAfterCode) > 0;
            }, ARRAY_FILTER_USE_KEY);
        }

        return array_slice(array_values($entityRecords), 0, $limit);
    }
}
