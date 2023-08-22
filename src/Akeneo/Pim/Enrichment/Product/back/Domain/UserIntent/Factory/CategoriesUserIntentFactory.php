<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoriesUserIntentFactory implements UserIntentFactory
{
    use ValidateDataTrait;

    public function getSupportedFieldNames(): array
    {
        return ['categories'];
    }

    public function create(string $fieldName, mixed $data): array
    {
        if (null === $data) {
            throw InvalidPropertyTypeException::arrayExpected($fieldName, static::class, $data);
        }
        $this->validateScalarArray($fieldName, $data);

        return [new SetCategories($data)];
    }
}
