<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StandardFormatToUserIntentsStub
{
    public function convert(array $data): array
    {
        return [new SetLabel('en_US', 'My label')];
    }
}
