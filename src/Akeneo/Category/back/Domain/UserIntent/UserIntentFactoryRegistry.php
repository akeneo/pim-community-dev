<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\UserIntent;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\UserIntent\Factory\UserIntentFactory;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserIntentFactoryRegistry
{
    /**
     * @var array<string, UserIntentFactory>
     */
    private array $userIntentFactoriesWithFieldName;

    /**
     * @param iterable<UserIntentFactory> $userIntentFactories
     * @param string[] $ignoredFieldNames
     */
    public function __construct(iterable $userIntentFactories, private array $ignoredFieldNames)
    {
        Assert::allString($ignoredFieldNames);

        foreach ($userIntentFactories as $userIntentFactory) {
            Assert::isInstanceOf($userIntentFactory, UserIntentFactory::class);
            $fieldNames = $userIntentFactory->getSupportedFieldNames();
            foreach ($fieldNames as $fieldName) {
                $this->userIntentFactoriesWithFieldName[$fieldName] = $userIntentFactory;
            }
        }
    }

    /**
     * @return UserIntent[]
     */
    public function fromStandardFormatField(string $fieldName, int $categoryId, mixed $data): array
    {
        $factory = $this->userIntentFactoriesWithFieldName[$fieldName] ?? null;
        if (\in_array($fieldName, $this->ignoredFieldNames)) {
            return [];
        }
        if (null === $factory) {
            throw new \InvalidArgumentException(\sprintf('Cannot create userIntent from %s fieldName', $fieldName));
        }

        return $factory->create($fieldName, $categoryId, $data);
    }
}
