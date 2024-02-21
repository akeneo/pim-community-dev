<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyUserIntentFactory implements UserIntentFactory
{
    public function getSupportedFieldNames(): array
    {
        return ['family'];
    }

    /**
     * @inheritDoc
     */
    public function create(string $fieldName, mixed $data): array
    {
        if (null === $data || '' === $data) {
            return [new RemoveFamily()];
        }
        if (!is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected($fieldName, static::class, $data);
        }

        return [new SetFamily($data)];
    }
}
