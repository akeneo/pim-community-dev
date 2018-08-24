<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeRegex
{
    public const NONE = null;

    /** @var ?string */
    private $regex;

    private function __construct(?string $regex)
    {
        $this->regex = $regex;
    }

    public static function fromString(string $regex): self
    {
        return new self($regex);
    }

    public static function none(): self
    {
        return new self(self::NONE);
    }

    public function isNone(): bool
    {
        return self::NONE === $this->regex;
    }

    public function normalize(): ?string
    {
        return $this->regex;
    }
}
