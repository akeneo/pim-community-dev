<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject;

use Webmozart\Assert\Assert;

final class ReferenceEntityIdentifier
{
    private string $identifier;

    private function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public static function fromString(string $identifier): self
    {
        Assert::stringNotEmpty($identifier);

        return new self($identifier);
    }

    public function asString(): string
    {
        return $this->identifier;
    }

    public function equals(ReferenceEntityIdentifier $otherIdentifier): bool
    {
        return \strtolower($this->identifier) === \strtolower($otherIdentifier->asString());
    }
}
