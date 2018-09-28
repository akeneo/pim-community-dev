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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordDetails;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class RecordDetailsHydrator implements RecordDetailsHydratorInterface
{
    /** @var AbstractPlatform */
    private $platform;

    public function __construct(Connection $connection)
    {
        $this->platform = $connection->getDatabasePlatform();
    }

    public function hydrate(array $row, array $emptyValues): RecordDetails
    {
        $labels = json_decode($row['labels'], true);
        $valueCollection = json_decode($row['value_collection'], true);
        $recordIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['identifier'], $this->platform);
        $enrichedEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['enriched_entity_identifier'], $this->platform);
        $recordCode = Type::getType(Type::STRING)
            ->convertToPHPValue($row['code'], $this->platform);

        $allValues = [];
        foreach ($emptyValues as $key => $value) {
            if (key_exists($key, $valueCollection)) {
                $value['data'] = $valueCollection[$key]['data'];
            }

            $allValues[] = $value;
        }

        $recordImage = Image::createEmpty();

        if (isset($row['image'])) {
            $image = json_decode($row['image'], true);
            $imageKey = Type::getType(Type::STRING)
                ->convertToPHPValue($image['file_key'], $this->platform);
            $imageFilename = Type::getType(Type::STRING)
                ->convertToPHPValue($image['original_filename'], $this->platform);
            $file = new FileInfo();
            $file->setKey($imageKey);
            $file->setOriginalFilename($imageFilename);
            $recordImage = Image::fromFileInfo($file);
        }

        $recordDetails = new RecordDetails(
            RecordIdentifier::fromString($recordIdentifier),
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
            RecordCode::fromString($recordCode),
            LabelCollection::fromArray($labels),
            $recordImage,
            $allValues
        );

        return $recordDetails;
    }
}
