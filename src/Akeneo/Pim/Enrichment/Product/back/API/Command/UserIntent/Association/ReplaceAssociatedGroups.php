<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReplaceAssociatedGroups implements AssociationUserIntent
{
    /**
     * @param array<string> $groupCodes
     */
    public function __construct(
        private string $associationType,
        private array  $groupCodes,
    ) {
        Assert::allStringNotEmpty($groupCodes);
        Assert::stringNotEmpty($this->associationType);
    }

    public function associationType(): string
    {
        return $this->associationType;
    }

    /**
     * @return array<string>
     */
    public function groupCodes(): array
    {
        return $this->groupCodes;
    }
}
