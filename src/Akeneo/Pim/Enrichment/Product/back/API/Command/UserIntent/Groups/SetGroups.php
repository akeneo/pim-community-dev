<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetGroups implements GroupUserIntent
{
    /**
     * @param array<string> $groupCodes
     */
    public function __construct(private array $groupCodes)
    {
        Assert::allStringNotEmpty($this->groupCodes);
    }

    public function groupCodes(): array
    {
        return $this->groupCodes;
    }
}
