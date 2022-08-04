<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StandardFormatToUserIntentsStub
{
    /**
     * @param array<string, mixed> $data
     * @return UserIntent[]
     */
    public function convert(array $data): array
    {
        return [new SetLabel('en_US', 'My label')];
    }
}
