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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityDetails;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityDetailsInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindReferenceEntityDetails implements FindReferenceEntityDetailsInterface
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
    public function __invoke(ReferenceEntityIdentifier $identifier): ?ReferenceEntityDetails
    {
        $result = $this->fetchResult($identifier);

        if (empty($result)) {
            return null;
        }

        return $this->hydrateReferenceEntityDetails(
            $result['identifier'],
            $result['labels'],
            $result['file_key'],
            $result['original_filename']);
    }

    private function fetchResult(ReferenceEntityIdentifier $identifier): array
    {
        $query = <<<SQL
        SELECT ee.identifier, ee.labels, fi.file_key, fi.original_filename
        FROM akeneo_reference_entity_reference_entity as ee
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
     * @param ?string $fileKey
     * @param ?string $originalFilename
     *
     * @return ReferenceEntityDetails
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function hydrateReferenceEntityDetails(
        string $identifier,
        string $normalizedLabels,
        ?string $fileKey,
        ?string $originalFilename
    ): ReferenceEntityDetails {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $entityImage = Image::createEmpty();

        if (null !== $fileKey && null !== $originalFilename) {
            $file = new FileInfo();
            $file->setKey($fileKey);
            $file->setOriginalFilename($originalFilename);
            $entityImage=Image::fromFileInfo($file);
        }

        $referenceEntityItem = new ReferenceEntityDetails();
        $referenceEntityItem->identifier = ReferenceEntityIdentifier::fromString($identifier);
        $referenceEntityItem->labels = LabelCollection::fromArray($labels);
        $referenceEntityItem->image = $entityImage;

        return $referenceEntityItem;
    }
}
