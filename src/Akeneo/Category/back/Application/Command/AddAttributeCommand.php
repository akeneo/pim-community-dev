<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddAttributeCommand
{
    private function __construct(
        public readonly string $code,
        public readonly string $locale,
        public readonly ?string $label,
        public readonly string $type,
        public readonly bool $isScopable,
        public readonly bool $isLocalizable,
        public readonly string $templateUuid,
    ) {
        Assert::uuid($templateUuid);
    }

    public static function create(
        string $code,
        string $type,
        bool $isScopable,
        bool $isLocalizable,
        string $templateUuid,
        string $locale,
        ?string $label,
    ): self {
        return new self(
            code: $code,
            locale: $locale,
            label: $label,
            type: $type,
            isScopable: $isScopable,
            isLocalizable: $isLocalizable,
            templateUuid: $templateUuid,
        );
    }
}
