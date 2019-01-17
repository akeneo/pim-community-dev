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

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindConnectorRecordsByIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindConnectorRecordsByIdentifiers implements FindConnectorRecordsByIdentifiersInterface
{
    /** @var ConnectorRecord[] */
    private $recordsByIdentifier;

    public function __construct()
    {
        $this->recordsByIdentifier = [];
    }

    public function save(RecordIdentifier $recordIdentifier, ConnectorRecord $connectorRecord): void
    {
        $this->recordsByIdentifier[(string) $recordIdentifier] = $connectorRecord;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $identifiers, RecordQuery $recordQuery): array
    {
        $records = [];

        foreach ($identifiers as $identifier) {
            if (isset($this->recordsByIdentifier[$identifier])) {
                $records[] = $this->filterRecordValues($this->recordsByIdentifier[$identifier], $recordQuery);
            }
        }

        return $records;
    }

    private function filterRecordValues(ConnectorRecord $connectorRecord, RecordQuery $recordQuery): ConnectorRecord
    {
        $channelReference = $recordQuery->getChannelReferenceValuesFilter();
        if (!$channelReference->isEmpty()) {
            $connectorRecord = $connectorRecord->getRecordWithValuesFilteredOnChannel($channelReference->getIdentifier());
        }

        $localesIdentifiers = $recordQuery->getLocaleIdentifiersValuesFilter();
        if (!$localesIdentifiers->isEmpty()) {
            $connectorRecord = $connectorRecord->getRecordWithValuesFilteredOnLocales($localesIdentifiers);
        }

        return $connectorRecord;
    }
}
