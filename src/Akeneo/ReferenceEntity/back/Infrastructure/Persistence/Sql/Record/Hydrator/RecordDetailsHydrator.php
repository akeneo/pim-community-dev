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
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindCodesByIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
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

    /** @var FindCodesByIdentifiersInterface */
    private $findCodesByIdentifiers;

    /** @var FindValueKeysByAttributeTypeInterface */
    private $findValueKeysByAttributeType;

    public function __construct(
        Connection $connection,
        FindCodesByIdentifiersInterface $findCodesByIdentifiers,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType
    ) {
        $this->platform = $connection->getDatabasePlatform();
        $this->findCodesByIdentifiers = $findCodesByIdentifiers;
        $this->findValueKeysByAttributeType = $findValueKeysByAttributeType;
    }

    public function hydrate(array $row, array $emptyValues): RecordDetails
    {
        $attributeAsLabel = Type::getType(Type::STRING)->convertToPHPValue($row['attribute_as_label'], $this->platform);
        $attributeAsImage = Type::getType(Type::STRING)->convertToPHPValue($row['attribute_as_image'], $this->platform);
        $valueCollection = Type::getType(Type::JSON_ARRAY)->convertToPHPValue($row['value_collection'], $this->platform);
        $recordIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['identifier'], $this->platform);
        $referenceEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['reference_entity_identifier'], $this->platform);
        $recordCode = Type::getType(Type::STRING)
            ->convertToPHPValue($row['code'], $this->platform);

        $valueCollection = $this->replaceIdentifiersByCodes($valueCollection, $referenceEntityIdentifier);
        $allValues = $this->createEmptyValues($emptyValues, $valueCollection);

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

    private function createEmptyValues(array $emptyValues, array $valueCollection): array
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

    /**
     * TODO: If the front directly handles record identifier as data, then we can drop this method and its call
     */
    private function replaceIdentifiersByCodes(array $valueCollection, string $referenceEntityIdentifier): array
    {
        // Values keys for record/record collection values
        $recordsValueKeys = $this->findValueKeysByAttributeType->find(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            ['record', 'record_collection']
        );

        $onlyRecordsValues = array_intersect_key($valueCollection, array_flip($recordsValueKeys));

        if (empty($onlyRecordsValues)) {
            return $valueCollection;
        }

        // Get identifiers for which we have to retrieve the code
        $identifiers = [];
        foreach ($onlyRecordsValues as $value) {
            $data = is_array($value['data']) ? $value['data'] : [$value['data']];
            $identifiers = array_merge($identifiers, $data);
        }

        $identifiers = array_unique($identifiers);

        // Retrieve the codes
        $indexedCodes = $this->findCodesByIdentifiers->find($identifiers);

        // Replace identifiers by code in the value collection
        foreach ($onlyRecordsValues as $valueKey => $value) {
            if (is_array($value['data'])) {
                $value['data'] = array_map(function ($identifier) use ($indexedCodes) {
                    return $indexedCodes[$identifier];
                }, $value['data']);
            } else {
                $value['data'] = $indexedCodes[$value['data']];
            }

            $valueCollection[$valueKey] = $value;
        }

        return $valueCollection;
    }
}
