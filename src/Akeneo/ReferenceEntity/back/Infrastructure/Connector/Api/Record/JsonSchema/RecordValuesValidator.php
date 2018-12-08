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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;

/**
 * Validate the record values grouped by attribute type.
 * It's more efficient than validate the values one by one.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordValuesValidator
{
    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    /** @var RecordValueValidatorRegistry */
    private $recordValueValidatorRegistry;

    public function __construct(
        RecordValueValidatorRegistry $recordValueValidatorRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->recordValueValidatorRegistry = $recordValueValidatorRegistry;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
    }

    public function validate(ReferenceEntityIdentifier $referenceEntityIdentifier, array $normalizedRecord): array
    {
        $recordValues = $normalizedRecord['values'];
        $attributeCodesIndexedByTypes = $this->getAttributeCodesIndexedByType($referenceEntityIdentifier);
        $errors = [];

        foreach ($attributeCodesIndexedByTypes as $attributeType => $attributeCodes) {
            $recordValuesByType = array_intersect_key($recordValues, array_flip($attributeCodes));

            if (!empty($recordValuesByType)) {
                $recordValueValidator = $this->recordValueValidatorRegistry->getValidator($attributeType);
                $normalizedRecordWithFilteredValues = array_replace($normalizedRecord, ['values' => $recordValuesByType]);
                $errors = array_merge($errors, $recordValueValidator->validate($normalizedRecordWithFilteredValues));
            }
        }

        return $errors;
    }

    private function getAttributeCodesIndexedByType(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $attributes = ($this->findAttributesIndexedByIdentifier)($referenceEntityIdentifier);
        $attributeCodesIndexedByTypes = [];

        foreach ($attributes as $attribute) {
            $attributeCodesIndexedByTypes[get_class($attribute)][] = (string) $attribute->getCode();
        }

        return $attributeCodesIndexedByTypes;
    }
}
