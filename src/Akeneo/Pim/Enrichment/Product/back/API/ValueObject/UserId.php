<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\ValueObject;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserId
{
    private function __construct(private ?string $id)
    {}

    public static function fromId(?string $id): self
    {
        return new self($id);
    }

    public function id(): ?string
    {
        return $this->id;
    }
}
