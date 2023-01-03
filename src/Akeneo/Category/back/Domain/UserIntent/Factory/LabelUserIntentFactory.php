<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\Domain\UserIntent\Factory;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

class LabelUserIntentFactory implements UserIntentFactory
{
    public function getSupportedFieldNames(): array
    {
        return ['labels'];
    }

    public function create(string $fieldName, int $categoryId, mixed $data): array
    {
        if (false === \is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($fieldName, static::class, $data);
        }

        $userIntents = [];

        foreach ($data as $localeCode => $label) {
            $userIntents[] = new SetLabel($localeCode, $label);
        }

        return $userIntents;
    }
}
