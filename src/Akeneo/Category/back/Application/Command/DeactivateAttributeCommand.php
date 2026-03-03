<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeactivateAttributeCommand
{
    private function __construct(
        public readonly string $templateUuid,
        public readonly string $attributeUuid,
    ) {
        Assert::uuid($templateUuid);
        Assert::uuid($attributeUuid);
    }

    public static function create(
        string $templateUuid,
        string $attributeUuid,
    ): self {
        return new self(
            templateUuid: $templateUuid,
            attributeUuid: $attributeUuid,
        );
    }
}
