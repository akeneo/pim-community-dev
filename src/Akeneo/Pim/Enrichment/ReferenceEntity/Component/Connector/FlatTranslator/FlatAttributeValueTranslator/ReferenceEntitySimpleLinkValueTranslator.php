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
class ReferenceEntitySimpleLinkValueTranslator implements FlatAttributeValueTranslatorInterface
{
    /** @var FindRecordsLabelTranslationsInterface */
    private $findRecordsLabelTranslations;

    public function __construct(FindRecordsLabelTranslationsInterface $findRecordsLabelTranslations)
    {
        $this->findRecordsLabelTranslations = $findRecordsLabelTranslations;
    }

    public function supports(string $attributeType, string $columnName): bool
    {
        return  AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT === $attributeType;
    }

    public function translate(string $attributeCode, array $properties, array $recordCodes, string $locale): array
    {
        if (!isset($properties['reference_data_name'])) {
            throw new \LogicException(sprintf('Expected properties to have a reference data name to translate reference entity single link values to flat'));
        }

        $referenceEntityIdentifier = $properties['reference_data_name'];
        $recordLabels = $this->findRecordsLabelTranslations->find($referenceEntityIdentifier, $recordCodes, $locale);

        $result = [];
        foreach ($recordCodes as $index => $recordCode) {
            if (empty($recordCode)) {
                $result[$index] = $recordCode;
                continue;
            }

            $result[$index] = $recordLabels[$recordCode] ?? sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $recordCode);
        }

        return $result;
    }
}
