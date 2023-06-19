<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\UpdateAttributeCommand;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type LocalizedLabels from LabelCollection
 */
final class UpdateAttributeCommand
{
    /**
     * @phpstan-param LocalizedLabels $labels
     */
    private function __construct(
        public readonly string $attributeUuid,
        public readonly ?bool $isRichTextArea,
        public readonly ?array $labels,
    ) {
        if ($labels !== null) {
            Assert::isMap($labels);
            Assert::allNullOrString($labels);
        }
    }

    /**
     * @phpstan-param LocalizedLabels $labels
     */
    public static function create(
        string $attributeUuid,
        ?bool $isRichTextArea,
        ?array $labels,
    ): self {
        return new self(
            attributeUuid: $attributeUuid,
            isRichTextArea: $isRichTextArea,
            labels: $labels,
        );
    }
}
