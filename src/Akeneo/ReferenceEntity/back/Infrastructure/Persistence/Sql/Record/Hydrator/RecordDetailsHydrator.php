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
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\SqlFindRecordLinkValueKeys;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\SqlRecordsExists;
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

    /** @var SqlRecordsExists */
    private $recordsExists;

    /** @var SqlFindRecordLinkValueKeys */
    private $findRecordLinkValueKeys;

    public function __construct(
        Connection $connection,
        SqlRecordsExists $recordsExists,
        SqlFindRecordLinkValueKeys $findRecordLinkValueKeys
    ) {
        $this->platform = $connection->getDatabasePlatform();
        $this->recordsExists = $recordsExists;
        $this->findRecordLinkValueKeys = $findRecordLinkValueKeys;
    }

    public function hydrate(array $row, array $emptyValues): RecordDetails
    {
        $attributeAsLabel = Type::getType(Type::STRING)->convertToPHPValue($row['attribute_as_label'], $this->platform);
        $attributeAsImage = Type::getType(Type::STRING)->convertToPHPValue($row['attribute_as_image'], $this->platform);
        $valueCollection = Type::getType(Type::JSON_ARRAY)->convertToPHPValue($row['value_collection'],
            $this->platform);
        $recordIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['identifier'], $this->platform);
        $referenceEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['reference_entity_identifier'], $this->platform);
        $recordCode = Type::getType(Type::STRING)
            ->convertToPHPValue($row['code'], $this->platform);

        $allValues = $this->filterBrokenRecordLinks($referenceEntityIdentifier, $valueCollection);
        $allValues = $this->allValues($emptyValues, $allValues);

        $labels = $this->getLabelsFromValues($valueCollection, $attributeAsLabel);
        $recordImage = $this->getImage($valueCollection, $attributeAsImage);

        $recordDetails = new RecordDetails(
            RecordIdentifier::fromString($recordIdentifier),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            RecordCode::fromString($recordCode),
            LabelCollection::fromArray($labels),
            $recordImage,
            array_values($allValues),
            true
        );

        return $recordDetails;
    }

    private function allValues(array $emptyValues, array $valueCollection): array
    {
        $result = [];
        foreach ($emptyValues as $key => $value) {
            if (array_key_exists($key, $valueCollection)) {
                $value['data'] = $valueCollection[$key]['data'];
            }

            $result[] = $value;
        }

        return $result;
    }

    private function getLabelsFromValues($valueCollection, $attributeAsLabel): array
    {
        return array_reduce(
            $valueCollection,
            function (array $labels, array $value) use ($attributeAsLabel) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $localeCode = $value['locale'];
                    $label = (string) $value['data'];
                    $labels[$localeCode] = $label;
                }

                return $labels;
            },
            []
        );
    }

    private function getImage($valueCollection, $attributeAsImage): Image
    {
        $imageValue = array_filter(
            $valueCollection,
            function (array $value) use ($attributeAsImage) {
                return $value['attribute'] === $attributeAsImage;
            }
        );

        $result = Image::createEmpty();
        if (!empty($imageValue)) {
            $imageValue = current($imageValue);
            $file = new FileInfo();
            $file->setKey($imageValue['data']['filePath']);
            $file->setOriginalFilename($imageValue['data']['originalFilename']);
            $result = Image::fromFileInfo($file);
        }

        return $result;
    }

    private function getRecordCodes($value): array
    {
        if (is_array($value['data'])) {
            $recordCodes = $value['data'];
        } else {
            $recordCodes = [$value['data']];
        }

        return $recordCodes;
    }

    private function updateValuesWithExistingRecordsOnly(array $allValues, $existingRecord, $valueKey): array
    {
        if (empty($existingRecord)) {
            unset($allValues[$valueKey]);
        } else {
            $allValues[$valueKey]['data'] = $existingRecord;
        }

        return $allValues;
    }

    /**
     * When we hydrate a RecordDetails model, we need to remove from values every deleted records that are
     * linked through attribute of type "record" or "record_collection".
     *
     * @merge When merging on master (> 3.0), we should change this as we don't store code anymore on 3.1+. If any
     *        doubt, please ping me directly (Adrien Pétremann).
     */
    private function filterBrokenRecordLinks(
        string $referenceEntityIdentifier,
        array $valueCollection
    ): array {
        $valueKeysAndMetadata = $this->findRecordLinkValueKeys->fetch(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier)
        );

        foreach ($valueKeysAndMetadata as $valueKeyAndMetadata) {
            $valueKey = $valueKeyAndMetadata['value_key'];

            $value = $valueCollection[$valueKey] ?? null;
            if (null === $value) {
                continue;
            }

            $recordCodes = $this->getRecordCodes($value);
            $existingRecordCodes = $this->recordsExists->withReferenceEntityAndCodes(
                ReferenceEntityIdentifier::fromString($valueKeyAndMetadata['record_type']),
                $recordCodes
            );

            if ('record' === $valueKeyAndMetadata['attribute_type']) {
                $existingRecordCodes = current($existingRecordCodes);
            }

            $valueCollection = $this->updateValuesWithExistingRecordsOnly(
                $valueCollection,
                $existingRecordCodes,
                $valueKey
            );
        }

        return $valueCollection;
    }
}
