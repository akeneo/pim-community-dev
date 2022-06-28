<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Model;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @codeCoverageIgnore
 */
final class Catalog
{
    public function __construct(
        private string $id,
        private string $name,
        private string $ownerUsername,
        private bool $enabled,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOwnerUsername(): string
    {
        return $this->ownerUsername;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
