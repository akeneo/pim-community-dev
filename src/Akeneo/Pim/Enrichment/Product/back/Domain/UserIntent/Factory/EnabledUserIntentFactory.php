<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnabledUserIntentFactory implements UserIntentFactory
{
    public function getSupportedFieldNames(): array
    {
        return ['enabled'];
    }

    public function create(string $fieldName, mixed $data): array
    {
        if (!\is_bool($data)) {
            throw InvalidPropertyTypeException::booleanExpected($fieldName, static::class, $data);
        }

        return [new SetEnabled($data)];
    }
}
