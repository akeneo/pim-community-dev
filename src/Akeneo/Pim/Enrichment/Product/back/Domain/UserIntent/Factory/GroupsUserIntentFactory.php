<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsUserIntentFactory implements UserIntentFactory
{
    use ValidateDataTrait;

    public function getSupportedFieldNames(): array
    {
        return ['groups'];
    }

    /**
     * @inheritDoc
     */
    public function create(string $fieldName, mixed $data): array
    {
        $this->validateScalarArray($fieldName, $data);

        if (null === $data) {
            throw InvalidPropertyTypeException::arrayExpected($fieldName, static::class, $data);
        }

        return [new SetGroups($data)];
    }
}
