<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueUserIntentFactoryRegistry implements UserIntentFactory
{
    /**
     * @var array<string, ValueUserIntentFactory>
     */
    private array $valueUserIntentFactoriesWithAttributeType;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param iterable<ValueUserIntentFactory> $valueUserIntentFactories
     */
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        iterable $valueUserIntentFactories
    ) {
        foreach ($valueUserIntentFactories as $valueUserIntentFactory) {
            Assert::isInstanceOf($valueUserIntentFactory, ValueUserIntentFactory::class);
            $attributeTypes = $valueUserIntentFactory->getSupportedAttributeTypes();
            foreach ($attributeTypes as $attributeType) {
                $this->valueUserIntentFactoriesWithAttributeType[$attributeType] = $valueUserIntentFactory;
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
    public function create(string $fieldName, mixed $data): UserIntent | array
    {
        Assert::isArray($data);

        $attributeTypesByCode = $this->attributeRepository->getAttributeTypeByCodes(\array_keys($data));
        $valueUserIntents = [];
        foreach ($data as $attributeCode => $values) {
            $attributeType = $attributeTypesByCode[$attributeCode] ?? null;
            $factory = $this->valueUserIntentFactoriesWithAttributeType[$attributeType] ?? null;
            if (null === $factory) {
                throw new \InvalidArgumentException('Not implemented');
            }
            foreach ($values as $value) {
                $valueUserIntents[] = $factory->create($attributeType, $attributeCode, $value);
            }
        }

        return $valueUserIntents;
    }
}
