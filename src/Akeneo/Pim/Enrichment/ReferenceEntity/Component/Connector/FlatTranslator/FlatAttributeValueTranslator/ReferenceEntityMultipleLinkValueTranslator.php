<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Connector\FlatTranslator\FlatAttributeValueTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslationsInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceEntityMultipleLinkValueTranslator implements FlatAttributeValueTranslatorInterface
{
    /** @var FindRecordsLabelTranslationsInterface */
    private $findRecordsLabelTranslations;

    public function __construct(FindRecordsLabelTranslationsInterface $findRecordsLabelTranslations)
    {
        $this->findRecordsLabelTranslations = $findRecordsLabelTranslations;
    }

    public function supports(string $attributeType, string $columnName): bool
    {
        return $attributeType === AttributeTypes::REFERENCE_ENTITY_COLLECTION;
    }

    public function translate(string $attributeCode, array $properties, array $values, string $locale): array
    {
        if (!isset($properties['reference_data_name'])) {
            throw new \LogicException(sprintf('Expected properties to have a reference data name to translate reference entity multiple link values to flat'));
        }

        $recordCodes = $this->extractRecordCodes($values);

        $referenceEntityIdentifier = $properties['reference_data_name'];
        $recordTranslations = $this->findRecordsLabelTranslations->find($referenceEntityIdentifier, $recordCodes, $locale);

        $result = [];
        foreach ($values as $valueIndex => $value) {
            if (empty($value)) {
                $result[$valueIndex] = $value;
                continue;
            }

            $currentRecordCodes = explode(',', $value);

            $recordLabels = [];
            foreach ($currentRecordCodes as $currentRecordCode) {
                $recordLabels[] = $recordTranslations[$currentRecordCode] ?? sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $currentRecordCode);
            }

            $result[$valueIndex] = implode(',', $recordLabels);
        }

        return $result;
    }

    private function extractRecordCodes(array $values): array
    {
        $recordCodes = [];

        foreach ($values as $value) {
            $currentRecordCodes = explode(',', $value);

            $recordCodes = array_merge($recordCodes, $currentRecordCodes);
        }

        return array_unique($recordCodes);
    }
}
