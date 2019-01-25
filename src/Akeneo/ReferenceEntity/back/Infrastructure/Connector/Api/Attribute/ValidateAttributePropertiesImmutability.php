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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeByIdentifierAndCodeInterface;

/**
 * Validate that the immutable properties of an attribute are not changed when editing it.
 */
class ValidateAttributePropertiesImmutability
{
    private const IMMUTABLE_PROPERTIES = [
        'type',
        'value_per_locale',
        'value_per_channel',
        'reference_entity_code',
    ];

    /** @var FindConnectorAttributeByIdentifierAndCodeInterface */
    private $findConnectorAttribute;

    public function __construct(FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttribute)
    {
        $this->findConnectorAttribute = $findConnectorAttribute;
    }

    /**
     * Returns the list of errors formatted as:
     * [
     *      'property' => 'reference_entity_code',
     *      'message'  => 'The property reference_entity_code is immutable.'
     * ]
     */
    public function __invoke(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode,
        array $editedProperties
    ): array {
        $attribute = ($this->findConnectorAttribute)($referenceEntityIdentifier, $attributeCode);
        if (null === $attribute) {
            throw new \RuntimeException(sprintf('Attribute %s was not found.', $editedProperties['code']));
        }

        $immutableEditedProperties = array_intersect_key($editedProperties, array_flip(self::IMMUTABLE_PROPERTIES));
        $originalValues = $attribute->normalize();
        $errors = [];

        foreach ($immutableEditedProperties as $immutableProperty => $editedValue) {
            if ($editedValue !== ($originalValues[$immutableProperty] ?? null)) {
                $errors[] = [
                    'property' => $immutableProperty,
                    'message'  => sprintf('The property %s is immutable.', $immutableProperty),
                ];
            }
        }

        return $errors;
    }
}
