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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityDetails;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\FindEnrichedEntityDetailsInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindEnrichedEntityDetails implements FindEnrichedEntityDetailsInterface
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
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __invoke(EnrichedEntityIdentifier $identifier): ?EnrichedEntityDetails
    {
        $result = $this->fetchResult($identifier);

        if (empty($result)) {
            return null;
        }

        return $this->hydrateEnrichedEntityDetails(
            $result['identifier'],
            $result['labels'],
            $result['file_path'],
            $result['original_filename']);
    }

    private function fetchResult(EnrichedEntityIdentifier $identifier): array
    {
        $query = <<<SQL
        SELECT ee.identifier, ee.labels, ee.image as file_path, fi.original_filename
        FROM akeneo_enriched_entity_enriched_entity as ee
        LEFT JOIN akeneo_file_storage_file_info AS fi ON fi.file_key = ee.image 
        WHERE ee.identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string)$identifier,
        ]);

        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return !$result ? [] : $result;
    }

    /**
     * @param string  $identifier
     * @param string  $normalizedLabels
     * @param ?string $filePath
     * @param ?string $originalFilename
     *
     * @return EnrichedEntityDetails
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function hydrateEnrichedEntityDetails(
        string $identifier,
        string $normalizedLabels,
        ?string $filePath,
        ?string $originalFilename
    ): EnrichedEntityDetails {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $file = null;

        if (null !== $filePath && null !== $originalFilename) {
            $file = new FileInfo();
            $file->setKey($filePath);
            $file->setOriginalFilename($originalFilename);
        }

        $enrichedEntityItem = new EnrichedEntityDetails();
        $enrichedEntityItem->identifier = EnrichedEntityIdentifier::fromString($identifier);
        $enrichedEntityItem->labels = LabelCollection::fromArray($labels);
        $enrichedEntityItem->image = (null !== $file) ? Image::fromFileInfo($file) : null;

        return $enrichedEntityItem;
    }
}
