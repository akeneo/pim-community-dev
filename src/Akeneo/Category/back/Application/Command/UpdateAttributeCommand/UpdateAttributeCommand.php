<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\UpdateAttributeCommand;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateAttributeCommand
{
    private function __construct(
        public readonly string $attributeUuid,
        public readonly bool $isRichTextArea,
    ) {
        Assert::uuid($attributeUuid);
    }

    public static function create(
        string $attributeUuid,
        bool $isRichTextArea,
    ): self {
        return new self(
            attributeUuid: $attributeUuid,
            isRichTextArea: $isRichTextArea,
        );
    }
}
