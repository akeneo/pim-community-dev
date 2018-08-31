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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindRecordDetails implements FindRecordDetailsInterface
{
    /** @var Connection */
    private $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(RecordIdentifier $recordIdentifier): ?RecordDetails
    {
        $result = $this->fetchResult($recordIdentifier);

        if (empty($result)) {
            return null;
        }

        $recordDetails = $this->hydrateRecordDetails(
            $result['identifier'],
            $result['enriched_entity_identifier'],
            $result['code'],
            $result['labels']
        );

        return $recordDetails;
    }

    private function fetchResult(RecordIdentifier $recordIdentifier): array
    {
        $query = <<<SQL
        SELECT identifier, code, enriched_entity_identifier, labels
        FROM akeneo_enriched_entity_record
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $recordIdentifier
        ]);
        $result = $statement->fetch();
        $statement->closeCursor();

        return !$result ? [] : $result;
    }

    private function hydrateRecordDetails(
        string $identifier,
        string $enrichedEntityIdentifier,
        string $code,
        string $normalizedLabels
    ): RecordDetails {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $enrichedEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($enrichedEntityIdentifier, $platform);
        $code = Type::getType(Type::STRING)->convertToPHPValue($code, $platform);

        $recordDetails = new RecordDetails();
        $recordDetails->identifier = RecordIdentifier::fromString($identifier);
        $recordDetails->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier);
        $recordDetails->code = RecordCode::fromString($code);
        $recordDetails->labels = LabelCollection::fromArray($labels);

        return $recordDetails;
    }
}
