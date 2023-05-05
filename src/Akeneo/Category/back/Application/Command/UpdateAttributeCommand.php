<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateAttributeCommand
{
    private function __construct(
        public readonly string $attributeUuid,
        public readonly string $code,
        public readonly int $order,
        public readonly string $locale,
        public readonly ?string $label,
        public readonly string $type,
        public readonly bool $isRequired,
        public readonly bool $isScopable,
        public readonly bool $isLocalizable,
        public readonly string $templateUuid,
    ) {
        Assert::uuid($attributeUuid);
        Assert::uuid($templateUuid);
    }

    public static function create(
        string $attributeUuid,
        string $code,
        int $order,
        string $locale,
        ?string $label,
        string $type,
        bool $isRequired,
        bool $isScopable,
        bool $isLocalizable,
        string $templateUuid,
    ): self {
        return new self(
            attributeUuid: $attributeUuid,
            code: $code,
            order: $order,
            locale: $locale,
            label: $label,
            type: $type,
            isRequired: $isRequired,
            isScopable: $isScopable,
            isLocalizable: $isLocalizable,
            templateUuid: $templateUuid,
        );
    }
}
