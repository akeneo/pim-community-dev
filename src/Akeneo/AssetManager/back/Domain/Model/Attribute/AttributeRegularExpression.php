<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeRegularExpression
{
    public const EMPTY = null;

    private ?string $regularExpression = null;

    private function __construct(?string $regularExpression)
    {
        if (null !== $regularExpression && false === @preg_match($regularExpression, '')) {
            throw new \InvalidArgumentException(
                sprintf('Expect a valid regular expression, "%s" given', $regularExpression)
            );
        }
        $this->regularExpression = $regularExpression;
    }

    public static function fromString(string $regularExpression): self
    {
        return new self($regularExpression);
    }

    public static function createEmpty(): self
    {
        return new self(self::EMPTY);
    }

    public function isEmpty(): bool
    {
        return self::EMPTY === $this->regularExpression;
    }

    public function __toString(): string
    {
        return $this->regularExpression ?? '';
    }

    public function normalize(): ?string
    {
        return $this->regularExpression;
    }
}
