<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\UserIntent\Factory;

use Akeneo\Category\Api\Command\UserIntents\SetImage;
use Akeneo\Category\Api\Command\UserIntents\SetRichText;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
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
    public function __construct(private readonly GetAttribute $getAttribute)
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
     * @phpstan-param array<string, AttributeValueApi> $data
     *
     * @return array|UserIntent[]
     */
    public function create(string $fieldName, int $categoryId, mixed $data): array
    {
        if (!\is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($fieldName, ValueUserIntentFactory::class, $data);
        }

        $attributeCollection = $this->getAttributeCollectionByAttributeValues($data);

        $userIntents = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $attributeType = $this->getAttributeType($attributeCollection, $value);
                // /!\ No attribute type found for the value. Do nothing for now.
                if (null === $attributeType) {
                    continue;
                }

                if (is_string($value['data']) && $this->isDataStringEmpty($value['data'])) {
                    $value['data'] = null;
                }

                $userIntents[] = $this->addValueUserIntent($attributeType, $value);
            }
        }

        return $userIntents;
    }

    /**
     * Sanitize data from html tags and special characters.
     * (i.e <p>data</p>\n will return "data").
     */
    private function isDataStringEmpty(string $data): bool
    {
        return empty(trim(strip_tags($data)));
    }

    /**
     * @param array<string, AttributeValueApi> $attributes
     */
    private function getAttributeCollectionByAttributeValues(array $attributes): AttributeCollection
    {
        if (!$attributes) {
            return AttributeCollection::fromArray([]);
        }

        $categoryAttributeUuids = $this->extractCategoryAttributeUuids(array_keys($attributes));

        return $this->getAttribute->byUuids($categoryAttributeUuids);
    }

    /**
     * Get a list of category attribute uuids from a local composite key list.
     *
     * @param array<string> $localeCompositeKeys (example: ['code|uuid|channel|locale'])
     *
     * @return AttributeUuid[]
     */
    private function extractCategoryAttributeUuids(array $localeCompositeKeys): array
    {
        // Get uuids
        $categoryAttributeUuids = array_map(function (string $keyWithLocale) {
            $uuid = explode(AbstractValue::SEPARATOR, $keyWithLocale)[1];

            return AttributeUuid::fromString($uuid);
        }, $localeCompositeKeys);

        return array_unique($categoryAttributeUuids);
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
     *     channel: string|null,
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
     *     channel: string|null,
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
            AttributeType::TEXTAREA => new SetTextArea($uuid, $code, $value['channel'], $value['locale'], $value['data']),
            AttributeType::RICH_TEXT => new SetRichText($uuid, $code, $value['channel'], $value['locale'], $value['data']),
            AttributeType::TEXT => new SetText($uuid, $code, $value['channel'], $value['locale'], $value['data']),
            AttributeType::IMAGE => new SetImage($uuid, $code, $value['channel'], $value['locale'], $value['data']),
            default => throw new \InvalidArgumentException('Not implemented')
        };
    }
}
