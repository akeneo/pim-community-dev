<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
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

    public function create(string $fieldName, mixed $data): UserIntent|array
    {
        if (null === $data) {
            return new SetGroups([]);
        }
        $this->validateScalarArray($fieldName, $data);

        return new SetGroups($data);
    }
}
