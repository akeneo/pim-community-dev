<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\ReorderTemplateAttributesCommand;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReorderTemplateAttributesCommand
{
    /**
     * @param array<string> $attributeUuids
     */
    private function __construct(
        public readonly string $templateUuid,
        public readonly array $attributeUuids,
    ) {
        Assert::uuid($templateUuid);
        Assert::AllString($attributeUuids);
    }

    /**
     * @param array<string> $attributeUuids
     */
    public static function create(
        string $templateUuid,
        array $attributeUuids,
    ): self {
        return new self(
            templateUuid: $templateUuid,
            attributeUuids: $attributeUuids,
        );
    }
}
