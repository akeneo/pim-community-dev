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
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindConnectorRecordByReferenceEntityAndCodeInterface;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindConnectorRecordByReferenceEntityAndCode implements FindConnectorRecordByReferenceEntityAndCodeInterface
{
    /** @var ConnectorRecord[] */
    private $results;

    public function __construct()
    {
        $this->results = [];
    }

    public function save(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $recordCode,
        ConnectorRecord $connectorRecord
    ): void {
        $this->results[sprintf('%s____%s', $referenceEntityIdentifier, $recordCode)] = $connectorRecord;
    }

    /**
     * {@inheritdoc}
     */
    public function find(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $recordCode
    ): ?ConnectorRecord {
        return $this->results[sprintf('%s____%s', $referenceEntityIdentifier, $recordCode)] ?? null;
    }
}
