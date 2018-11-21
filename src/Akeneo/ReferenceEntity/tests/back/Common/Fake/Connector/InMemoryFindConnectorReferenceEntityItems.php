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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityItemsInterface;

class InMemoryFindConnectorReferenceEntityItems implements FindConnectorReferenceEntityItemsInterface
{
    /** @var ConnectorReferenceEntity[] */
    private $results;

    public function __construct()
    {
        $this->results = [];
    }

    public function save(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ConnectorReferenceEntity $connectorReferenceEntity
    ): void {
        $this->results[(string) $referenceEntityIdentifier] = $connectorReferenceEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($query): array {
        // @TODO - do pagination here using size from query
        // Filter on search after then apply limit

//        $searchAfterCode = $query->getSearchAfterCode();
//        $records = array_values(array_filter($records, function (Record $record) use ($searchAfterCode): bool {
//            return null === $searchAfterCode
//                || strcasecmp((string) $record->getCode(), $searchAfterCode) > 0;
//        }));
//
//        usort($records, function ($firstRecord, $secondRecord) {
//            return strcasecmp((string) $firstRecord->getCode(), (string) $secondRecord->getCode());
//        });
//
//        $records = array_slice($records, 0, $query->getSize());

        return $this->results;
    }
}
