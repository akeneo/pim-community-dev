<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Domain\UserIntent\UserIntentFactoryRegistry;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StandardFormatToUserIntents implements StandardFormatToUserIntentsInterface
{
    public function __construct(private UserIntentFactoryRegistry $userIntentFactoryRegistry)
    {
    }

    /**
     * @param array<string, mixed> $standardFormat
     *
     * @return UserIntent[]
     */
    public function convert(array $standardFormat): array
    {
        $userIntents = [];
        $categoryId = $standardFormat['id'];
        foreach ($standardFormat as $fieldName => $data) {
            $result = $this->userIntentFactoryRegistry->fromStandardFormatField($fieldName, $categoryId, $data);
            $userIntents = \array_merge($userIntents, $result);
        }

        return \array_filter($userIntents);
    }
}
