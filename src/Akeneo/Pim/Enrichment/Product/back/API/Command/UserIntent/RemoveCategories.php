<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RemoveCategories implements CategoryUserIntent
{
    /** @param array<string> $categoryCodes */
    public function __construct(private array $categoryCodes)
    {
        Assert::notEmpty($categoryCodes);
        Assert::allStringNotEmpty($this->categoryCodes);
    }

    /** @return array<string> */
    public function categoryCodes(): array
    {
        return $this->categoryCodes;
    }
}
