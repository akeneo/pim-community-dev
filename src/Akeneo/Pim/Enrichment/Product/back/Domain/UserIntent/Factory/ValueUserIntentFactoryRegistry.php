<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueUserIntentFactoryRegistry implements UserIntentFactory
{
    private iterable $valueUserIntentFactories;

    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        iterable $valueUserIntentFactories
    ) {
        foreach ($valueUserIntentFactories as $valueUserIntentFactory) {
            Assert::isInstanceOf($valueUserIntentFactory, ValueUserIntentFactory::class);
            $attributeTypes = $valueUserIntentFactory->getSupportedAttributeTypes();
            foreach ($attributeTypes as $attributeType) {
                $this->valueUserIntentFactories[$attributeType] = $valueUserIntentFactory;
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

        $attributeTypesByCode = $this->attributeRepository->getAttributeTypeByCodes(\array_keys($data));
        $valueUserIntents = [];
        foreach ($data as $attributeCode => $values) {
            // TODO: check what we do if null (throw exception)
            $attributeType = $attributeTypesByCode[$attributeCode] ?? null;
            // TODO: check what we do if null (throw exception)
            $factory = $this->valueUserIntentFactories[$attributeType] ?? null;
            if (!\is_array($values)) {
                throw InvalidPropertyTypeException::arrayExpected($attributeCode, static::class, $values);
            }
            foreach ($values as $value) {
                $valueUserIntents[] = $factory->create($attributeType, $attributeCode, $value);
            }
        }

        return $valueUserIntents;
    }
}
