<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\UserIntent\Factory;

use Akeneo\Category\Api\Command\UserIntents\SetImage;
use Akeneo\Category\Api\Command\UserIntents\SetRichText;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\Query\GetAttributeInMemory;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Infrastructure\Converter\InternalApi\InternalApiToStd;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-import-type AttributeValueApi from InternalApiToStd
 */
final class ValueUserIntentFactory implements UserIntentFactory
{
    public function __construct(private GetAttributeInMemory $getAttributeInMemory)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedFieldNames(): array
    {
        return ['values'];
    }

    /**
     * @param array<string, AttributeValueApi> $data
     *
     * @return array|UserIntent[]
     */
    public function create(string $fieldName, mixed $data): array
    {
        if (!\is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($fieldName, ValueUserIntentFactory::class, $data);
        }

        $attributeCollection = $this->getAttributeCollectionByCodes($data);

        $userIntents = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $attributeType = $this->getAttributeType($attributeCollection, $value);
                // /!\ No attribute type found for the value. Do nothing for now.
                if (null === $attributeType) {
                    continue;
                }

                $userIntents[] = $this->addValueUserIntent($attributeType, $value);
            }
        }

        return $userIntents;
    }

    /**
     * @param array<string, AttributeValueApi> $attributes
     */
    private function getAttributeCollectionByCodes(array $attributes): AttributeCollection
    {
        $compositeKeys = $this->extractCompositeKeys(array_keys($attributes));

        return $this->getAttributeInMemory->byIdentifiers($compositeKeys);
    }

    /**
     * Get a list of composite key from the local composite key list given in parameter.
     *
     * @param array<string> $localeCompositeKeys (example: ['code|uuid|locale'])
     *
     * @return array<string> (example: ['code|uuid'])
     */
    private function extractCompositeKeys(array $localeCompositeKeys): array
    {
        // Get keys, check unicity and rebuild it
        $compositeKeys = array_map(function ($keyWithLocale) {
            $exploded = explode(AbstractValue::SEPARATOR, $keyWithLocale);
            // build the composite key ('code|uuid') and return it
            return $exploded[0].AbstractValue::SEPARATOR.$exploded[1];
        }, $localeCompositeKeys);

        return array_unique($compositeKeys);
    }

    /**
     * @param array{
     *     data: array{
     *      size: int,
     *      extension: string,
     *      file_path: string,
     *      mime_type: string,
     *      original_filename: string,
     *     } | string | null,
     *     locale: string|null,
     *     attribute_code: string
     * } $value
     */
    private function getAttributeType(AttributeCollection $attributeCollection, array $value): ?AttributeType
    {
        $attribute = $attributeCollection->getAttributeByIdentifier($value['attribute_code']);

        return $attribute?->getType();
    }

    /**
     * @param array{
     *     data: array{
     *      size: int,
     *      extension: string,
     *      file_path: string,
     *      mime_type: string,
     *      original_filename: string,
     *     } | string | null,
     *     locale: string|null,
     *     attribute_code: string
     * } $value
     */
    private function addValueUserIntent(AttributeType $attributeType, array $value): UserIntent
    {
        $identifiers = explode(AbstractValue::SEPARATOR, $value['attribute_code']);
        if (count($identifiers) !== 2) {
            throw new \InvalidArgumentException(sprintf('Cannot set value user intent %s : no identifier found', $attributeType));
        }
        $uuid = $identifiers[1];
        $code = $identifiers[0];

        return match ((string) $attributeType) {
            AttributeType::TEXTAREA => new SetTextArea($uuid, $code, $value['locale'], $value['data']),
            AttributeType::RICH_TEXT => new SetRichText($uuid, $code, $value['locale'], $value['data']),
            AttributeType::TEXT => new SetText($uuid, $code, $value['locale'], $value['data']),
            AttributeType::IMAGE => new SetImage($uuid, $code, $value['locale'], $value['data']),
            default => throw new \InvalidArgumentException('Not implemented')
        };
    }
}
