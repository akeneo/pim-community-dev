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

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityDetails;
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

    /** @var FindAttributesDetailsInterface */
    private $findAttributesDetails;

    public function __construct(Connection $sqlConnection, FindAttributesDetailsInterface $findAttributesDetails)
    {
        $this->sqlConnection = $sqlConnection;
        $this->findAttributesDetails = $findAttributesDetails;
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

        $attributesDetails = ($this->findAttributesDetails)($identifier);

        return $this->hydrateReferenceEntityDetails(
            $result['identifier'],
            $result['labels'],
            $result['record_count'],
            $result['file_key'],
            $result['original_filename'],
            $attributesDetails
        );
    }

    private function fetchResult(ReferenceEntityIdentifier $identifier): array
    {
        $query = <<<SQL
        SELECT
            re.identifier,
            re.labels,
            fi.file_key,
            fi.original_filename, (
                SELECT count(*) FROM akeneo_reference_entity_record WHERE reference_entity_identifier = :identifier
            ) as record_count
        FROM akeneo_reference_entity_reference_entity as re
        LEFT JOIN akeneo_file_storage_file_info AS fi ON fi.file_key = re.image
        WHERE re.identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $identifier,
        ]);

        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return !$result ? [] : $result;
    }

    /**
     * @return ReferenceEntityDetails
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function hydrateReferenceEntityDetails(
        string $identifier,
        string $normalizedLabels,
        string $recordCount,
        ?string $fileKey,
        ?string $originalFilename,
        array $attributesDetails
    ): ReferenceEntityDetails {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = Type::getType(Type::JSON_ARRAY)->convertToPHPValue($normalizedLabels, $platform);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $recordCount = Type::getType(Type::INTEGER)->convertToPHPValue($recordCount, $platform);

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
        $referenceEntityItem->recordCount = $recordCount;
        $referenceEntityItem->attributes = $attributesDetails;

        return $referenceEntityItem;
    }
}
