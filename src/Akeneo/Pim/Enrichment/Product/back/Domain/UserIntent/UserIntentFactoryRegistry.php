<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\UserIntentFactory;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserIntentFactoryRegistry
{
    private iterable $userIntentFactories;

    public function __construct(iterable $userIntentFactories, private array $ignoredFieldNames)
    {
        Assert::allString($ignoredFieldNames);

        foreach ($userIntentFactories as $userIntentFactory) {
            Assert::isInstanceOf($userIntentFactory, UserIntentFactory::class);
            $fieldNames = $userIntentFactory->getSupportedFieldNames();
            foreach ($fieldNames as $fieldName) {
                $this->userIntentFactories[$fieldName] = $userIntentFactory;
            }
        }
    }

    public function fromStandardFormatField(string $fieldName, mixed $data): UserIntent | array | null
    {
        $factory = $this->userIntentFactories[$fieldName] ?? null;
        if (null === $factory && \in_array($fieldName, $this->ignoredFieldNames)) {
            return null;
        }
        if (null === $factory) {
            throw new \InvalidArgumentException(\sprintf('Cannot create userIntent from %s fieldName', $fieldName));
        }

        return $factory->create($fieldName, $data);
    }
}
