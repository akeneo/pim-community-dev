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
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindRecordsForConnectorByIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\RecordForConnector;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordsForConnectorByIdentifiers implements FindRecordsForConnectorByIdentifiersInterface
{
    /** @var RecordForConnector[] */
    private $recordsByIdentifier;

    public function __construct()
    {
        $this->recordsByIdentifier = [];
    }

    public function save(RecordIdentifier $recordIdentifier, RecordForConnector $recordForConnector): void
    {
        $this->recordsByIdentifier[(string) $recordIdentifier] = $recordForConnector;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $identifiers): array
    {
        $records = [];

        foreach ($identifiers as $identifier) {
            if (isset($this->recordsByIdentifier[$identifier])) {
                $records[] = $this->recordsByIdentifier[$identifier];
            }
        }

        return $records;
    }
}
