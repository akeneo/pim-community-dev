<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeactivateTemplateCommand
{
    private function __construct(
        private readonly string $uuid,
    ) {
        Assert::uuid($uuid);
    }

    public static function create(string $uuid): self
    {
        return new self($uuid);
    }

    public function uuid(): string
    {
        return $this->uuid;
    }
}
