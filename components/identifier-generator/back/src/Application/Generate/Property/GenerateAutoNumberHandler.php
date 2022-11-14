<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateAutoNumberHandler
{
    public function __invoke(AutoNumber $autoNumber): string
    {
        // TODO CPM-804: Get the next identifier to generate
        return '' . rand(0, 10000);
    }
}
