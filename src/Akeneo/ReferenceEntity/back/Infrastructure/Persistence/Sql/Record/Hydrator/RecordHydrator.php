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
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class RecordHydrator implements RecordHydratorInterface
{
    /** @var ValueHydratorInterface */
    private $valueHydrator;

    /** @var AbstractPlatform */
    private $platform;

    public function __construct(Connection $connection, ValueHydratorInterface $valueHydrator)
    {
        $this->valueHydrator = $valueHydrator;
        $this->platform = $connection->getDatabasePlatform();
    }

    public function hydrate(array $row, ValueKeyCollection $valueKeyCollection, array $attributes): Record
    {
        $labels = json_decode($row['labels'], true);
        $valueCollection = json_decode($row['value_collection'], true);
        $recordIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['identifier'], $this->platform);
        $referenceEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['reference_entity_identifier'], $this->platform);
        $recordCode = Type::getType(Type::STRING)
            ->convertToPHPValue($row['code'], $this->platform);

        $hydratedValues = [];
        foreach ($valueKeyCollection as $valueKey) {
            $key = (string) $valueKey;
            if (!array_key_exists($key, $valueCollection)) {
                continue;
            }

            $rawValue = $valueCollection[$key];
            $attributeIdentifier = $rawValue['attribute'];
            $hydratedValues[$key] = $this->valueHydrator->hydrate(
                $rawValue,
                $attributes[$attributeIdentifier]
            );
        }

        $recordImage = Image::createEmpty();

        if (isset($row['image'])) {
            $recordImage =  $this->hydrateImage(json_decode($row['image'], true));
        }

        $record = Record::create(
            RecordIdentifier::fromString($recordIdentifier),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            RecordCode::fromString($recordCode),
            $labels,
            $recordImage,
            ValueCollection::fromValues($hydratedValues)
        );

        return $record;
    }

    private function hydrateImage(array $imageData): Image
    {
        $imageKey = Type::getType(Type::STRING)
            ->convertToPHPValue($imageData['file_key'], $this->platform);
        $imageFilename = Type::getType(Type::STRING)
            ->convertToPHPValue($imageData['original_filename'], $this->platform);

        $file = new FileInfo();
        $file->setKey($imageKey);
        $file->setOriginalFilename($imageFilename);

        return Image::fromFileInfo($file);
    }
}
