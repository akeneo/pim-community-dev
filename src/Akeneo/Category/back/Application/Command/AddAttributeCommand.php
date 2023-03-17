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
        private readonly string $code,
        private readonly string $locale,
        private readonly ?string $label,
        private readonly string $type,
        private readonly bool $isScopable,
        private readonly bool $isLocalizable,
        private readonly string $templateUuid,
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

    public function code(): string
    {
        return $this->code;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function label(): ?string
    {
        return $this->label;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isScopable(): bool
    {
        return $this->isScopable;
    }

    public function isLocalizable(): bool
    {
        return $this->isLocalizable;
    }

    public function templateUuid(): string
    {
        return $this->templateUuid;
    }
}
