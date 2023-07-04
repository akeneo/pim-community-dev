<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetAttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueUserIntentFactoryRegistry implements UserIntentFactory
{
    /** @var array<string, ValueUserIntentFactory> */
    private array $valueUserIntentFactoriesByAttributeType = [];

    /**
     * @param iterable<ValueUserIntentFactory> $valueUserIntentFactories
     */
    public function __construct(
        private GetAttributeTypes $getAttributeTypes,
        iterable $valueUserIntentFactories
    ) {
        foreach ($valueUserIntentFactories as $valueUserIntentFactory) {
            Assert::isInstanceOf($valueUserIntentFactory, ValueUserIntentFactory::class);
            $attributeTypes = $valueUserIntentFactory->getSupportedAttributeTypes();
            foreach ($attributeTypes as $attributeType) {
                $this->valueUserIntentFactoriesByAttributeType[$attributeType] = $valueUserIntentFactory;
            }
        }
    }

    public function getSupportedFieldNames(): array
    {
        return ['values'];
    }

    /**
     * @inerhitDoc
     */
    public function create(string $fieldName, mixed $data): array
    {
        Assert::isArray($data);

        $attributeTypesByCode = $this->getAttributeTypesByCode($data);
        $valueUserIntents = [];
        foreach ($data as $attributeCode => $values) {
            $attributeType = $attributeTypesByCode[\strtolower((string) $attributeCode)] ?? null;
            if (null === $attributeType) {
                throw UnknownPropertyException::unknownProperty($attributeCode);
            }
            $factory = $this->valueUserIntentFactoriesByAttributeType[$attributeType] ?? null;
            if (null === $factory) {
                throw new \InvalidArgumentException(\sprintf('There is no value factory linked to the attribute type %s', $attributeType));
            }
            foreach ($values as $value) {
                $valueUserIntents[] = $factory->create($attributeType, (string) $attributeCode, $value);
            }
        }

        return $valueUserIntents;
    }

    /**
     * @return string[]
     */
    private function getAttributeTypesByCode(mixed $data): array
    {
        $attributeCodes = \array_map(static fn ($attributeCode) => (string) $attributeCode, \array_keys($data));
        return \array_change_key_case($this->getAttributeTypes->fromAttributeCodes($attributeCodes), \CASE_LOWER);
    }
}
