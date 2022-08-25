<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\UserIntent\Factory;

use Akeneo\Category\Api\Command\UserIntents\SetRichText;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\Query\GetAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\Converter\InternalAPI\InternalAPIToStd;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-import-type AttributeCodeApi from InternalAPIToStd
 * @phpstan-import-type AttributeValueApi from InternalAPIToStd
 */
final class ValueUserIntentFactory implements UserIntentFactory
{
    public function __construct(private GetAttribute $getAttribute)
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
     * @param array<string, mixed> $data
     *
     * @return array|UserIntent[]
     */
    public function create(string $fieldName, mixed $data): array
    {
        if (!\is_array($data) || !array_key_exists('attribute_codes', $data) || empty($data['attribute_codes'])) {
            throw InvalidPropertyTypeException::arrayExpected($fieldName, static::class, $data);
        }

        $attributeCollection = $this->getAttributeCollectionByCodes($data['attribute_codes']);

        /** @var array<string, AttributeValueApi> $attributeValues */
        $attributeValues = array_filter(
            $data,
            static fn ($attributeKey) => $attributeKey !== 'attribute_codes',
            ARRAY_FILTER_USE_KEY,
        );

        $userIntents = [];
        if (!empty($attributeValues)) {
            foreach ($attributeValues as $value) {
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
     * @param array<string> $attributeCodes
     */
    private function getAttributeCollectionByCodes(array $attributeCodes): AttributeCollection
    {
        return $this->getAttribute->byIdentifiers($attributeCodes);
    }

    /**
     * @param array{data: string, locale: string|null, attribute_code: string} $value
     */
    private function getAttributeType(AttributeCollection $attributeCollection, array $value): ?string
    {
        $attribute = array_filter(
            $attributeCollection->normalize(),
            static function ($attribute) use ($value) {
                $identifier = implode(ValueCollection::SEPARATOR, [$attribute['code'], $attribute['identifier']]);

                return $identifier == $value['attribute_code'];
            },
        );
        if (empty($attribute) || count($attribute) > 1) {
            return null;
        }

        return (current($attribute))['type'];
    }

    /**
     * @param array{data: string, locale: string|null, attribute_code: string} $value
     */
    private function addValueUserIntent(string $attributeType, array $value): UserIntent
    {
        $identifiers = explode(ValueCollection::SEPARATOR, $value['attribute_code']);
        if (count($identifiers) !== 2) {
            throw new \InvalidArgumentException(sprintf('Cannot set value user intent %s : no identifier found', $attributeType));
        }
        $uuid = $identifiers[1];
        $code = $identifiers[0];

        return match ($attributeType) {
            AttributeType::TEXTAREA => new SetTextArea($uuid, $code, $value['locale'], $value['data']),
            AttributeType::RICH_TEXT => new SetRichText($uuid, $code, $value['locale'], $value['data']),
            default => throw new \InvalidArgumentException('Not implemented')
        };
    }
}
