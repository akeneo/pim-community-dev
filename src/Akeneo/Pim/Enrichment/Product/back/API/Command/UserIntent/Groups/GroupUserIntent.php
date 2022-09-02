<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GroupUserIntent extends UserIntent
{
    /** @return array<string> */
    public function groupCodes(): array;
}
