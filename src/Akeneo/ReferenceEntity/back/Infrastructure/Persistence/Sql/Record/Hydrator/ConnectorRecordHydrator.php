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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\ConnectorValueTransformerRegistry;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorRecordHydrator
{
    /** @var AbstractPlatform */
    private $platform;

    /** @var ConnectorValueTransformerRegistry */
    private $valueTransformerRegistry;

    public function __construct(
        Connection $connection,
        ConnectorValueTransformerRegistry $valueTransformerRegistry
    ) {
        $this->platform = $connection->getDatabasePlatform();
        $this->valueTransformerRegistry = $valueTransformerRegistry;
    }

    public function hydrate(array $row, ValueKeyCollection $valueKeyCollection, array $attributes): ConnectorRecord
    {
        $labels = Type::getType(Type::JSON_ARRAY)
            ->convertToPHPValue($row['labels'], $this->platform);
        $valueCollection = Type::getType(Type::JSON_ARRAY)
            ->convertToPHPValue($row['value_collection'], $this->platform);
        $recordCode = Type::getType(Type::STRING)
            ->convertToPHPValue($row['code'], $this->platform);

        $filteredRawValues = [];
        foreach ($valueKeyCollection as $valueKey) {
            $key = (string) $valueKey;
            if (!array_key_exists($key, $valueCollection)) {
                continue;
            }

            $filteredRawValues[$key] = $valueCollection[$key];
        }

        $normalizedValues = $this->normalizeValues($filteredRawValues, $attributes);

        $recordImage = Image::createEmpty();
        if (isset($row['image_file_key'])) {
            $recordImage =  $this->hydrateImage($row);
        }

        $connectorRecord = new ConnectorRecord(
            RecordCode::fromString($recordCode),
            LabelCollection::fromArray($labels),
            $recordImage,
            $normalizedValues
        );

        return $connectorRecord;
    }

    private function hydrateImage(array $imageData): Image
    {
        $imageKey = Type::getType(Type::STRING)
            ->convertToPHPValue($imageData['image_file_key'], $this->platform);
        $imageFilename = Type::getType(Type::STRING)
            ->convertToPHPValue($imageData['image_original_filename'], $this->platform);

        $file = new FileInfo();
        $file->setKey($imageKey);
        $file->setOriginalFilename($imageFilename);

        return Image::fromFileInfo($file);
    }

    private function normalizeValues(array $rawValues, array $attributes): array
    {
        $normalizedValues = [];

        foreach ($rawValues as $key => $rawValue) {
            $attributeIdentifier = $rawValue['attribute'];
            Assert::notNull($attributes[$attributeIdentifier] ?? null, sprintf(
                'Attribute not found for the identifier %s', $attributeIdentifier
            ));

            $attribute = $attributes[$attributeIdentifier];
            $attributeCode = (string) $attribute->getCode();
            $valueTransformer = $this->valueTransformerRegistry->getTransformer($attribute);

            $normalizedValues[$attributeCode][] = $valueTransformer->transform($rawValue);
        }

        return $normalizedValues;
    }
}
