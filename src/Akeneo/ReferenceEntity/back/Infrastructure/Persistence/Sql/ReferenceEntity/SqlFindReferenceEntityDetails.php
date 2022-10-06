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

use Doctrine\DBAL\Exception;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Locale\FindActivatedLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityDetails;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindReferenceEntityDetails implements FindReferenceEntityDetailsInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private FindAttributesDetailsInterface $findAttributesDetails,
        private FindActivatedLocalesInterface $findActivatedLocales
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function find(ReferenceEntityIdentifier $identifier): ?ReferenceEntityDetails
    {
        $result = $this->fetchResult($identifier);

        if (empty($result)) {
            return null;
        }

        $attributesDetails = $this->findAttributesDetails->find($identifier);

        return $this->hydrateReferenceEntityDetails(
            $result['identifier'],
            $result['labels'],
            $result['record_count'],
            $result['file_key'],
            $result['original_filename'],
            $attributesDetails,
            $result['attribute_as_label'],
            $result['attribute_as_image']
        );
    }

    private function fetchResult(ReferenceEntityIdentifier $identifier): array
    {
        $query = <<<SQL
        SELECT
            re.identifier,
            re.labels,
            re.attribute_as_label,
            re.attribute_as_image,
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

        $result = $statement->fetchAssociative();
        $statement->free();

        return $result ?: [];
    }

    /**
     * @throws Exception
     */
    private function hydrateReferenceEntityDetails(
        string $identifier,
        string $normalizedLabels,
        string $recordCount,
        ?string $fileKey,
        ?string $originalFilename,
        array $attributesDetails,
        ?string $attributeAsLabel,
        ?string $attributeAsImage
    ): ReferenceEntityDetails {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $activatedLocales = $this->findActivatedLocales->findAll();

        $labels = Type::getType(Types::JSON)->convertToPHPValue($normalizedLabels, $platform);
        $identifier = Type::getType(Types::STRING)->convertToPHPValue($identifier, $platform);
        $recordCount = Type::getType(Types::INTEGER)->convertToPHPValue($recordCount, $platform);

        $entityImage = Image::createEmpty();
        if (null !== $fileKey && null !== $originalFilename) {
            $file = new FileInfo();
            $file->setKey($fileKey);
            $file->setOriginalFilename($originalFilename);
            $entityImage=Image::fromFileInfo($file);
        }

        $labelsByActivatedLocales = $this->getLabelsByActivatedLocales($labels, $activatedLocales);

        $referenceEntityItem = new ReferenceEntityDetails();
        $referenceEntityItem->identifier = ReferenceEntityIdentifier::fromString($identifier);
        $referenceEntityItem->labels = LabelCollection::fromArray($labelsByActivatedLocales);
        $referenceEntityItem->image = $entityImage;
        $referenceEntityItem->recordCount = $recordCount;
        $referenceEntityItem->attributes = $attributesDetails;
        $referenceEntityItem->attributeAsLabel = AttributeAsLabelReference::createFromNormalized($attributeAsLabel);
        $referenceEntityItem->attributeAsImage = AttributeAsImageReference::createFromNormalized($attributeAsImage);

        return $referenceEntityItem;
    }

    private function getLabelsByActivatedLocales(array $labels, array $activatedLocales): array
    {
        $filteredLabels = [];
        foreach ($labels as $localeCode => $label) {
            if (in_array($localeCode, $activatedLocales)) {
                $filteredLabels[$localeCode] = $label;
            }
        }

        return $filteredLabels;
    }
}
