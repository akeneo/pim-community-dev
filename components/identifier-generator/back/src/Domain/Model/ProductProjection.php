<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductProjection
{
    public function __construct(
        private ?string $identifier,
        private bool $enabled,
    ) {
    }

    public function identifier(): ?string
    {
        return $this->identifier;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }
}
