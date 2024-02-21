<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ConvertToSimpleProduct;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParentUserIntentFactory implements UserIntentFactory
{
    public function getSupportedFieldNames(): array
    {
        return ['parent'];
    }

    /**
     * @inheritDoc
     */
    public function create(string $fieldName, mixed $data): array
    {
        if (null === $data || '' === $data) {
            return [new ConvertToSimpleProduct()];
        }
        if (!\is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected($fieldName, static::class, $data);
        }

        return [new ChangeParent($data)];
    }
}
