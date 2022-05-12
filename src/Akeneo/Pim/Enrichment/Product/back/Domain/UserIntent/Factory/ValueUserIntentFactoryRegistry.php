<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
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
        foreach ($data as $attributeCode => $value) {
            // TODO: check what we do if null (throw exception)
            $attributeType = $attributeTypesByCode[$attributeCode] ?? null;
            // TODO: check what we do if null (throw exception)
            $factory = $this->valueUserIntentFactories[$attributeType] ?? null;
            $valueUserIntents[] = $factory->create($attributeType, $attributeCode, $value);
        }

        return $valueUserIntents;
    }
}
