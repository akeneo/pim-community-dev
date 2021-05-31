<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * Value that represents a unique, immutable identifier of a user or other security principal.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SecurityIdentifier
{
    private string $identifier;

    private function __construct(string $identifier)
    {
        Assert::stringNotEmpty($identifier);
        $this->identifier = $identifier;
    }

    public static function fromString(string $identifier): self
    {
        return new self($identifier);
    }

    public function stringValue(): string
    {
        return $this->identifier;
    }
}
